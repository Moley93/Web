const express = require('express');
const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');
const db = require('../config/database');
const { authenticateToken } = require('../middleware/auth');

const router = express.Router();

// Create MoonPay transaction
router.post('/create-transaction', authenticateToken, async (req, res) => {
    try {
        const { orderTotal, cryptoCurrency, userEmail } = req.body;
        
        // Convert GBP to USD (you should use a real currency API)
        const gbpToUsd = 1.27; // Example rate
        const usdAmount = orderTotal * gbpToUsd;
        
        // Get wallet address based on crypto currency
        const walletAddresses = {
            'btc': process.env.BTC_WALLET,
            'eth': process.env.ETH_WALLET,
            'ltc': process.env.LTC_WALLET,
            'xrp': process.env.XRP_WALLET
        };

        const walletAddress = walletAddresses[cryptoCurrency.toLowerCase()];
        if (!walletAddress) {
            return res.status(400).json({ error: 'Unsupported cryptocurrency' });
        }

        // Create MoonPay transaction
        const transactionData = {
            apiKey: process.env.MOONPAY_API_KEY,
            currencyCode: cryptoCurrency.toLowerCase(),
            baseCurrencyAmount: usdAmount,
            baseCurrencyCode: 'usd',
            walletAddress: walletAddress,
            redirectURL: `${process.env.FRONTEND_URL}/payment-success`,
            externalTransactionId: uuidv4()
        };

        // Generate MoonPay signature
        const signature = generateMoonPaySignature(transactionData);
        
        // Build MoonPay URL
        const moonpayUrl = buildMoonPayUrl(transactionData, signature);
        
        res.json({
            success: true,
            paymentUrl: moonpayUrl,
            externalTransactionId: transactionData.externalTransactionId
        });

    } catch (error) {
        console.error('MoonPay transaction creation error:', error);
        res.status(500).json({ error: 'Failed to create payment transaction' });
    }
});

// MoonPay webhook handler
router.post('/webhook', async (req, res) => {
    try {
        const signature = req.headers['moonpay-signature'];
        const payload = JSON.stringify(req.body);
        
        // Verify webhook signature
        const expectedSignature = crypto
            .createHmac('sha256', process.env.MOONPAY_SECRET_KEY)
            .update(payload)
            .digest('hex');

        if (signature !== expectedSignature) {
            return res.status(401).json({ error: 'Invalid signature' });
        }

        const { type, data } = req.body;

        if (type === 'transaction_completed') {
            // Update order status in database
            await db.execute(
                'UPDATE orders SET status = ?, moonpay_transaction_id = ? WHERE id = ?',
                ['paid', data.id, data.externalTransactionId]
            );
            
            console.log(`Order ${data.externalTransactionId} payment confirmed`);
        }

        res.status(200).json({ success: true });
    } catch (error) {
        console.error('Webhook error:', error);
        res.status(500).json({ error: 'Webhook processing failed' });
    }
});

// Helper functions
function generateMoonPaySignature(data) {
    const queryString = Object.keys(data)
        .sort()
        .map(key => `${key}=${encodeURIComponent(data[key])}`)
        .join('&');
    
    return crypto
        .createHmac('sha256', process.env.MOONPAY_SECRET_KEY)
        .update(queryString)
        .digest('base64');
}

function buildMoonPayUrl(data, signature) {
    const baseUrl = 'https://buy.moonpay.com';
    const params = new URLSearchParams(data);
    params.append('signature', signature);
    return `${baseUrl}?${params.toString()}`;
}

module.exports = router;