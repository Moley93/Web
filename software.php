<?php
// software.php - Software Recommendations Page
$page_title = "Software Solutions";
require_once 'includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    <!-- Hero Section -->
    <section style="text-align: center; margin-bottom: 4rem;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; color: var(--text-dark);">Software Solutions</h1>
        <p style="font-size: 1.25rem; color: var(--text-light); max-width: 700px; margin: 0 auto;">
            Professional software development tools and platforms to bring your hardware projects to life. 
            We recommend the best software solutions for embedded development, IoT applications, and more.
        </p>
    </section>

    <!-- Development Environments -->
    <section style="margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Development Environments</h2>
        
        <div style="display: grid; gap: 2rem;">
            
            <!-- IDE 1: Visual Studio Code -->
            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); display: flex; align-items: center;">
                <div style="background: linear-gradient(135deg, #007ACC, #005a9e); width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; flex-shrink: 0;">
                    <i class="fas fa-code"></i>
                </div>
                <div style="padding: 2rem; flex: 1;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Visual Studio Code + PlatformIO</h3>
                    <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                        The ultimate combination for embedded development. VS Code provides a modern editing experience 
                        while PlatformIO adds comprehensive embedded development capabilities for 800+ boards.
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Free</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Cross-Platform</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Extensions</span>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="https://code.visualstudio.com/" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download VS Code
                        </a>
                        <div style="color: var(--text-light); font-size: 0.875rem;">
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <span style="margin-left: 0.5rem;">Most Popular</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IDE 2: Arduino IDE -->
            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); display: flex; align-items: center;">
                <div style="background: linear-gradient(135deg, #00979D, #007a80); width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; flex-shrink: 0;">
                    <i class="fas fa-microchip"></i>
                </div>
                <div style="padding: 2rem; flex: 1;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">Arduino IDE 2.0</h3>
                    <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                        The classic choice for Arduino development, now with a modern interface. Perfect for beginners 
                        and rapid prototyping with extensive library support and community resources.
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Beginner Friendly</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Library Manager</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Open Source</span>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="https://www.arduino.cc/en/software" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Download Arduino IDE
                        </a>
                        <div style="color: var(--text-light); font-size: 0.875rem;">
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: var(--border-color);"></i>
                            <span style="margin-left: 0.5rem;">Best for Beginners</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IDE 3: IAR Embedded Workbench -->
            <div style="background: white; border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow); display: flex; align-items: center;">
                <div style="background: linear-gradient(135deg, #FF6B35, #e55a2b); width: 150px; height: 150px; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; flex-shrink: 0;">
                    <i class="fas fa-industry"></i>
                </div>
                <div style="padding: 2rem; flex: 1;">
                    <h3 style="margin-bottom: 1rem; font-size: 1.5rem;">IAR Embedded Workbench</h3>
                    <p style="color: var(--text-light); margin-bottom: 1rem; line-height: 1.6;">
                        Professional-grade development suite for complex embedded projects. Industry standard for 
                        commercial development with advanced debugging, code optimization, and safety certifications.
                    </p>
                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Professional</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Safety Certified</span>
                        <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Commercial</span>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <a href="https://www.iar.com/" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt"></i> Visit IAR
                        </a>
                        <div style="color: var(--text-light); font-size: 0.875rem;">
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <i class="fas fa-star" style="color: #ffd700;"></i>
                            <span style="margin-left: 0.5rem;">Industry Standard</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- IoT Platforms -->
    <section style="background: var(--bg-light); padding: 3rem; border-radius: 1rem; margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">IoT & Cloud Platforms</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow);">
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: #232F3E; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; margin-right: 1rem;">
                        <i class="fab fa-aws"></i>
                    </div>
                    <h3 style="margin: 0;">AWS IoT Core</h3>
                </div>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; line-height: 1.6;">
                    Comprehensive cloud platform for IoT applications with device management, 
                    data processing, and machine learning capabilities.
                </p>
                <div style="margin-bottom: 1rem;">
                    <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; margin-right: 0.5rem;">Scalable</span>
                    <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Secure</span>
                </div>
                <a href="https://aws.amazon.com/iot-core/" target="_blank" class="btn btn-outline">Learn More</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow);">
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: #0078D4; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; margin-right: 1rem;">
                        <i class="fab fa-microsoft"></i>
                    </div>
                    <h3 style="margin: 0;">Azure IoT</h3>
                </div>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; line-height: 1.6;">
                    Microsoft's IoT platform with edge computing capabilities, 
                    digital twins, and enterprise-grade security features.
                </p>
                <div style="margin-bottom: 1rem;">
                    <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; margin-right: 0.5rem;">Enterprise</span>
                    <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Edge Computing</span>
                </div>
                <a href="https://azure.microsoft.com/en-us/services/iot/" target="_blank" class="btn btn-outline">Learn More</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow);">
                <div style="display: flex; align-items: center; margin-bottom: 1.5rem;">
                    <div style="width: 60px; height: 60px; background: #EA4335; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; color: white; margin-right: 1rem;">
                        <i class="fab fa-google"></i>
                    </div>
                    <h3 style="margin: 0;">Google Cloud IoT</h3>
                </div>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; line-height: 1.6;">
                    Google's IoT platform with powerful analytics, 
                    machine learning integration, and global infrastructure.
                </p>
                <div style="margin-bottom: 1rem;">
                    <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; margin-right: 0.5rem;">AI/ML</span>
                    <span style="background: var(--bg-light); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem;">Analytics</span>
                </div>
                <a href="https://cloud.google.com/iot" target="_blank" class="btn btn-outline">Learn More</a>
            </div>
        </div>
    </section>

    <!-- Programming Languages -->
    <section style="margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Recommended Programming Languages</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🇨</div>
                <h3 style="margin-bottom: 1rem;">C/C++</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    The gold standard for embedded programming. Direct hardware control, 
                    optimal performance, and widespread support across all platforms.
                </p>
                <div style="margin-bottom: 1rem;">
                    <span style="background: var(--success-color); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">Best Performance</span>
                </div>
                <a href="https://www.learncpp.com/" target="_blank" class="btn btn-outline btn-sm">Learn C++</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🐍</div>
                <h3 style="margin-bottom: 1rem;">Python</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Perfect for rapid prototyping, data analysis, and IoT applications. 
                    Great for beginners with extensive libraries and community support.
                </p>
                <div style="margin-bottom: 1rem;">
                    <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">Beginner Friendly</span>
                </div>
                <a href="https://micropython.org/" target="_blank" class="btn btn-outline btn-sm">MicroPython</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">☕</div>
                <h3 style="margin-bottom: 1rem;">Java</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Excellent for enterprise IoT applications, Android development, 
                    and cross-platform solutions with robust security features.
                </p>
                <div style="margin-bottom: 1rem;">
                    <span style="background: var(--warning-color); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">Enterprise</span>
                </div>
                <a href="https://www.oracle.com/java/" target="_blank" class="btn btn-outline btn-sm">Learn Java</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🦀</div>
                <h3 style="margin-bottom: 1rem;">Rust</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Modern systems programming with memory safety. Growing support 
                    for embedded development with excellent performance.
                </p>
                <div style="margin-bottom: 1rem;">
                    <span style="background: var(--error-color); color: white; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">Memory Safe</span>
                </div>
                <a href="https://www.rust-lang.org/" target="_blank" class="btn btn-outline btn-sm">Learn Rust</a>
            </div>
        </div>
    </section>

    <!-- Development Tools -->
    <section style="background: white; padding: 3rem; border-radius: 1rem; box-shadow: var(--shadow); margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Essential Development Tools</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div>
                <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-bug" style="color: var(--error-color);"></i> Debugging Tools
                </h3>
                <ul style="color: var(--text-light); line-height: 1.8;">
                    <li><strong>GDB:</strong> GNU Debugger for embedded systems</li>
                    <li><strong>OpenOCD:</strong> Open On-Chip Debugger</li>
                    <li><strong>Segger J-Link:</strong> Professional debugging probe</li>
                    <li><strong>ST-Link:</strong> STMicroelectronics debugging tool</li>
                </ul>
            </div>
            
            <div>
                <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-code-branch" style="color: var(--success-color);"></i> Version Control
                </h3>
                <ul style="color: var(--text-light); line-height: 1.8;">
                    <li><strong>Git:</strong> Distributed version control system</li>
                    <li><strong>GitHub:</strong> Cloud-based Git repository hosting</li>
                    <li><strong>GitLab:</strong> DevOps platform with CI/CD</li>
                    <li><strong>Bitbucket:</strong> Atlassian's Git solution</li>
                </ul>
            </div>
            
            <div>
                <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-chart-line" style="color: var(--primary-color);"></i> Testing & Analysis
                </h3>
                <ul style="color: var(--text-light); line-height: 1.8;">
                    <li><strong>Unity:</strong> C unit testing framework</li>
                    <li><strong>Cppcheck:</strong> Static analysis for C/C++</li>
                    <li><strong>Valgrind:</strong> Memory debugging tool</li>
                    <li><strong>SonarQube:</strong> Code quality analysis</li>
                </ul>
            </div>
            
            <div>
                <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fas fa-wrench" style="color: var(--warning-color);"></i> Build Systems
                </h3>
                <ul style="color: var(--text-light); line-height: 1.8;">
                    <li><strong>CMake:</strong> Cross-platform build system</li>
                    <li><strong>Make:</strong> Traditional build automation</li>
                    <li><strong>Ninja:</strong> Fast, small build system</li>
                    <li><strong>Bazel:</strong> Google's build tool</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Simulation Software -->
    <section style="margin-bottom: 4rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Circuit Simulation Software</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;">
                    <i class="fas fa-sitemap"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">KiCad</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Free, open-source PCB design suite with schematic capture, 
                    PCB layout, and 3D visualization capabilities.
                </p>
                <a href="https://www.kicad.org/" target="_blank" class="btn btn-outline">Download KiCad</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 3rem; color: var(--success-color); margin-bottom: 1rem;">
                    <i class="fas fa-microchip"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Tinkercad</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Browser-based circuit simulator perfect for beginners. 
                    Great for Arduino projects and learning electronics.
                </p>
                <a href="https://www.tinkercad.com/" target="_blank" class="btn btn-outline">Try Tinkercad</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 3rem; color: var(--warning-color); margin-bottom: 1rem;">
                    <i class="fas fa-desktop"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">Altium Designer</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Professional PCB design software with advanced features 
                    for complex, multi-layer board designs.
                </p>
                <a href="https://www.altium.com/" target="_blank" class="btn btn-outline">Learn More</a>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: var(--shadow); text-align: center;">
                <div style="font-size: 3rem; color: var(--error-color); margin-bottom: 1rem;">
                    <i class="fas fa-wave-square"></i>
                </div>
                <h3 style="margin-bottom: 1rem;">LTspice</h3>
                <p style="color: var(--text-light); margin-bottom: 1.5rem; font-size: 0.875rem;">
                    Free analog circuit simulator from Linear Technology. 
                    Excellent for analog circuit design and analysis.
                </p>
                <a href="https://www.analog.com/en/design-center/design-tools-and-calculators/ltspice-simulator.html" target="_blank" class="btn btn-outline">Download LTspice</a>
            </div>
        </div>
    </section>

    <!-- Getting Started Guide -->
    <section style="background: var(--bg-light); padding: 3rem; border-radius: 1rem;">
        <h2 style="text-align: center; margin-bottom: 3rem; font-size: 2.5rem;">Getting Started with Software Development</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">1</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Choose Your Hardware</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Start with a development board from our hardware collection 
                    that matches your project requirements.
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">2</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Set Up Development Environment</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Install your chosen IDE and configure it for your specific 
                    hardware platform and programming language.
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">3</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Start with Examples</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Begin with simple examples and tutorials to understand 
                    the basics before moving to complex projects.
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 80px; height: 80px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; color: white; font-size: 2rem;">
                    <span style="font-weight: bold;">4</span>
                </div>
                <h4 style="margin-bottom: 1rem;">Build & Deploy</h4>
                <p style="color: var(--text-light); font-size: 0.875rem;">
                    Compile your code, upload to your hardware, and test 
                    your application in real-world conditions.
                </p>
            </div>
        </div>
    </section>
</div>

<!-- Call to Action -->
<section style="background: linear-gradient(135deg, var(--primary-color), #3b82f6); color: white; padding: 4rem 0; margin-top: 4rem;">
    <div class="container" style="text-align: center;">
        <h2 style="font-size: 2.5rem; margin-bottom: 1rem;">Ready to Start Developing?</h2>
        <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.9;">
            Get the hardware you need and start building with our recommended software tools
        </p>
        
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="hardware.php" class="btn" style="background: white; color: var(--primary-color); font-weight: 600;">
                <i class="fas fa-shopping-bag"></i> Shop Hardware
            </a>
            <a href="mailto:software@vylo.co.uk" class="btn btn-outline" style="border-color: white; color: white;">
                <i class="fas fa-envelope"></i> Get Software Support
            </a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>