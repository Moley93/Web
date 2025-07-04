</main>

    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>VYLO</h3>
                    <p>Your trusted UK hardware specialist providing quality electronics and development boards with next-day delivery across the UK.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="hardware.php">Hardware Store</a></li>
                        <li><a href="firmware.php">Firmware Solutions</a></li>
                        <li><a href="software.php">Software Recommendations</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Customer Service</h4>
                    <ul>
                        <li><a href="shipping.php">Shipping Information</a></li>
                        <li><a href="returns.php">Returns & Exchanges</a></li>
                        <li><a href="faq.php">Frequently Asked Questions</a></li>
                        <li><a href="support.php">Technical Support</a></li>
                        <li><a href="track.php">Track Your Order</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Information</h4>
                    <div class="contact-info">
                        <p><i class="fas fa-envelope"></i> <?php echo SITE_EMAIL; ?></p>
                        <p><i class="fas fa-phone"></i> +44 (0) 123 456 7890</p>
                        <p><i class="fas fa-map-marker-alt"></i> United Kingdom</p>
                    </div>
                    <div class="delivery-info">
                        <p><strong>Next Day Delivery Available</strong></p>
                        <p>Order before 3PM for next business day delivery</p>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="footer-bottom-content">
                    <p>&copy; <?php echo date('Y'); ?> VYLO. All rights reserved.</p>
                    <div class="footer-links">
                        <a href="privacy.php">Privacy Policy</a>
                        <a href="terms.php">Terms of Service</a>
                        <a href="cookies.php">Cookie Policy</a>
                    </div>
                    <div class="payment-methods">
                        <span>We Accept:</span>
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-amex"></i>
                        <i class="fab fa-bitcoin"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
    
    <!-- Cart abandonment tracking -->
    <script>
        if (typeof cartAbandonmentTracking !== 'undefined') {
            cartAbandonmentTracking.init();
        }
    </script>
</body>
</html>