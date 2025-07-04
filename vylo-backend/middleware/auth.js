const jwt = require('jsonwebtoken');
const db = require('../config/database');

const authenticateToken = async (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        return res.status(401).json({ error: 'Access token required' });
    }

    try {
        const decoded = jwt.verify(token, process.env.JWT_SECRET);
        
        // Check if session exists in database
        const [sessions] = await db.execute(
            'SELECT user_id FROM user_sessions WHERE id = ? AND expires_at > NOW()',
            [decoded.sessionId]
        );

        if (sessions.length === 0) {
            return res.status(401).json({ error: 'Invalid or expired session' });
        }

        req.userId = sessions[0].user_id;
        req.sessionId = decoded.sessionId;
        next();
    } catch (error) {
        res.status(403).json({ error: 'Invalid token' });
    }
};

module.exports = { authenticateToken };