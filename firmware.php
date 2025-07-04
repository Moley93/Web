<?php
// firmware.php - Firmware Recommendations Page
$page_title = "Firmware Solutions";
require_once 'includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <!-- Hero Section -->
    <section style="text-align: center; margin-bottom: 4rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; color: var(--text-dark);">Firmware Solutions</h1>
        <p style="font-size: 1.25rem; color: var(--text-light); max-width: 700px; margin: 0 auto;">
            Professional firmware development and embedded solutions for your hardware projects. 
            We recommend trusted partners who specialize in creating robust, reliable firmware.
        </p>
    </section>

    <!-- What is Firmware Section -->
    <section style="background: white; padding: 3rem; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 3rem;">
        <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem;">What is Firmware?</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; align-items: center;">
            <div>
                <p style="font-size: 1.125rem; line-height: 1.7; margin-bottom: 1.5rem;">
                    Firmware is the low-level software that provides control, monitoring and data manipulation 
                    of engineered products and systems. It's the bridge between your hardware and higher-level software.
                </p>
                
                <ul style="font-size: 1rem; color: var(--text-light); line-height: 1.8;">
                    <li>Controls hardware components and peripherals</li>
                    <li>Manages power consumption and efficiency</li>
                    <li>Handles communication protocols (WiFi, Bluetooth, etc.)</li>
                    <li>Implements security features and encryption</li>
                    <li>Enables real-time processing and response</li>
                </ul>
            </div>
            
            <div style="text-align: center;">
                <div style="font-size: 8rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-microchip"></i>
                </div>
                <p style="color: var(--text-light); font-style: italic;">
                    "The soul of every smart device"
                </p>
            </div>
        </div>
    </section>

    <!-- Recommended Firmware Partners -->
    <section style="margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Recommended Firmware Partners</h2>
        
        <div style="display: grid; gap: 2rem;">
            
            <!-- Partner 1: Espressif Systems -->
            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); display: flex; align-items: center;">
                <div style="background: linear-gradient(135deg, #ff6b6b, #ee5a52); width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; flex-shrink: 0;">
                    <i class="fas fa-wifi"></i>
                </div>
                <div style="padding: 2rem; flex: 1;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Espressif Systems</h3>
                    <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                        Leading provider of ESP32 and ESP8266 firmware solutions. Specializes in IoT connectivity, 
                        WiFi integration, and low-power embedded systems for smart devices.
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">ESP-IDF</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Arduino Core</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">IoT Solutions</span>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="https://www.espressif.com/" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Visit Espressif
                        </a>
                        <div style="color: var(--text-light); font-size: 0.875rem;">
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <span style="margin-left: 0.5rem;">Highly Recommended</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partner 2: ARM Mbed -->
            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); display: flex; align-items: center;">
                <div style="background: linear-gradient(135deg, #4CAF50, #45a049); width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; flex-shrink: 0;">
                    <i class="fas fa-cogs"></i>
                </div>
                <div style="padding: 2rem; flex: 1;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">ARM Mbed Platform</h3>
                    <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                        Comprehensive IoT device platform for ARM Cortex-M microcontrollers. Provides tools, 
                        libraries, and cloud services for professional embedded development.
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Mbed OS</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Cloud Services</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Professional Tools</span>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="https://os.mbed.com/" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Visit Mbed
                        </a>
                        <div style="color: var(--text-light); font-size: 0.875rem;">
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: var(--border-color);"></i>
                            <span style="margin-left: 0.5rem;">Enterprise Ready</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partner 3: PlatformIO -->
            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); display: flex; align-items: center;">
                <div style="background: linear-gradient(135deg, #2196F3, #1976D2); width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; flex-shrink: 0;">
                    <i class="fas fa-code"></i>
                </div>
                <div style="padding: 2rem; flex: 1;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">PlatformIO</h3>
                    <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                        Cross-platform ecosystem for IoT development. Supports 800+ boards and frameworks, 
                        making it perfect for diverse embedded projects and rapid prototyping.
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Multi-Platform</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">VS Code</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">800+ Boards</span>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="https://platformio.org/" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Visit PlatformIO
                        </a>
                        <div style="color: var(--text-light); font-size: 0.875rem;">
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <span style="margin-left: 0.5rem;">Developer Favorite</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Partner 4: Zephyr Project -->
            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); display: flex; align-items: center;">
                <div style="background: linear-gradient(135deg, #9C27B0, #7B1FA2); width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; flex-shrink: 0;">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div style="padding: 2rem; flex: 1;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Zephyr Project</h3>
                    <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                        Real-time operating system (RTOS) designed for connected, resource-constrained devices. 
                        Backed by the Linux Foundation with strong security and safety features.
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">RTOS</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Linux Foundation</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Security Focus</span>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="https://www.zephyrproject.org/" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Visit Zephyr
                        </a>
                        <div style="color: var(--text-light); font-size: 0.875rem;">
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: var(--border-color);"></i>
                            <span style="margin-left: 0.5rem;">Industrial Grade</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom Firmware Development -->
    <section style="background: var(--bg-light); padding: 3rem; border-radius: 1rem; margin-bottom: 3rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Need Custom Firmware Development?</h2>
            <p style="font-size: 1.125rem; color: var(--text-light); max-width: 600px; margin: 0 auto;">
                For bespoke firmware solutions tailored to your specific hardware requirements
            </p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center;">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Freelance Developers</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Connect with experienced firmware developers for project-based work
                </p>
                <a href="https://www.upwork.com/hire/embedded-systems-engineers/" target="_blank" class="btn btn-outline">
                    Find Developers
                </a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center;">
                <div style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;">
                    <i class="fas fa-building"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Professional Services</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Full-service firmware development companies for complex projects
                </p>
                <a href="mailto:partnerships@vylo.co.uk" class="btn btn-outline">
                    Request Quote
                </a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center;">
                <div style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Training & Workshops</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem;">
                    Learn firmware development with hands-on training sessions
                </p>
                <a href="mailto:training@vylo.co.uk" class="btn btn-outline">
                    Learn More
                </a>
            </div>
        </div>
    </section>

    <!-- Firmware Development Process -->
    <section style="background: white; padding: 3rem; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 3rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Firmware Development Process</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">1</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Requirements Analysis</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Define functionality, performance requirements, and hardware constraints
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">2</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Architecture Design</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Create system architecture and define software modules and interfaces
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">3</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Development & Testing</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Code implementation with continuous testing and debugging cycles
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">4</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Deployment & Support</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Final integration, deployment, and ongoing maintenance support
                </p>
            </div>
        </div>
    </section>

    <!-- Compatible Hardware -->
    <section>
        <h2 style="text-align: center; margin-bottom: 2rem; font-size: 2.5rem;">Compatible Hardware from VYLO</h2>
        <p style="text-align: center; color: var(--text-light); margin-bottom: 3rem; font-size: 1.125rem;">
            Our firmware partners work with the hardware we stock
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-microchip"></i>
                </div>
                <h4 style="margin-bottom: 0.5rem;">ESP32 Boards</h4>
                <p style="color: var(--text-light); font-size: 0.875rem; margin-bottom: 1rem;">
                    WiFi & Bluetooth enabled microcontrollers
                </p>
                <a href="hardware.php?search=esp32" class="btn btn-outline btn-sm">Shop ESP32</a>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 2.5rem; color: var(--success-color); margin-bottom: 1rem;">
                    <i class="fas fa-memory"></i>
                </div>
                <h4 style="margin-bottom: 0.5rem;">ARM Cortex-M</h4>
                <p style="color: var(--text-light); font-size: 0.875rem; margin-bottom: 1rem;">
                    Professional grade microcontrollers
                </p>
                <a href="hardware.php?search=arm" class="btn btn-outline btn-sm">Shop ARM</a>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 2.5rem; color: var(--warning-color); margin-bottom: 1rem;">
                    <i class="fas fa-bolt"></i>
                </div>
                <h4 style="margin-bottom: 0.5rem;">Arduino Compatible</h4>
                <p style="color: var(--text-light); font-size: 0.875rem; margin-bottom: 1rem;">
                    Easy to program development boards
                </p>
                <a href="hardware.php?search=arduino" class="btn btn-outline btn-sm">Shop Arduino</a>
            </div>
            
            <div style="background: white; padding: 1.5rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 2.5rem; color: var(--error-color); margin-bottom: 1rem;">
                    <i class="fas fa-broadcast-tower"></i>
                </div>
                <h4 style="margin-bottom: 0.5rem;">IoT Modules</h4>
                <p style="color: var(--text-light); font-size: 0.875rem; margin-bottom: 1rem;">
                    Connected device development
                </p>
                <a href="hardware.php?search=iot" class="btn btn-outline btn-sm">Shop IoT</a>
            </div>
        </div>
    </section>
</div>

<!-- Call to Action -->
<section style="background: linear-gradient(135deg, var(--primary-color), #3b82f6); color: white; padding: 4rem 0; margin-top: 4rem;">
    <div class="container" style="text-align: center;">
        <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Start Your Firmware Project?</h2>
        <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.9;">
            Get the hardware you need and connect with our recommended firmware partners
        </p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="hardware.php" class="btn" style="background: white; color: var(--primary-color); font-weight: 600;">
                <i class="fas fa-shopping-bag"></i> Shop Hardware
            </a>
            <a href="mailto:firmware@vylo.co.uk" class="btn btn-outline" style="border-color: white; color: white;">
                <i class="fas fa-envelope"></i> Get Firmware Quote
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>