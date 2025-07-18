<!DOCTYPE html>
<html lang="en">
<?php include "header.php" ?>


    <div class="container">
        <div class="privacy-policy">
            <h1 class="main-title"> Contact us</h1> 
        </div>
    </div>
    
<!-- Contact Section Start -->
<div class="container py-5">
    
    
    <div class="row">
        <!-- Contact Form -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4">Get in Touch</h4>
                    <form id="contactForm">
                        <div class="mb-3">
                            <input type="text" class="form-control" id="name" placeholder="Your Name" required>
                        </div>
                        <div class="mb-3">
                            <input type="email" class="form-control" id="email" placeholder="Your Email" required>
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="subject" placeholder="Subject" required>
                        </div>
                        <div class="mb-3">
                            <textarea class="form-control" rows="5" id="message" placeholder="Message" required></textarea>
                        </div>
                        <div class="text-center">
                            <button class="btn btn-primary w-100 py-2" type="submit">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Contact Info & Map -->
        <div class="col-lg-5">
            <!-- Contact Details -->
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-body p-4">
                    <h4 class="text-center mb-3">Contact Information</h4>
                    
                    <p><i class="bi bi-envelope-fill text-primary me-2"></i> Razababasupport@gmail.com</p>
                    <p><i class="bi bi-telephone-fill text-primary me-2"></i> +92-32133242</p>
                </div>
            </div>

            <!-- Google Map -->
            <div class="card shadow-lg border-0">
                <div class="card-body p-0"> 
                    <iframe 
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d27224.152525178193!2d74.329108!3d31.5203694!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x391905d000000001%3A0xa21961d640b6177f!2sKalma%20Chowk%2C%20Lahore%2C%20Pakistan!5e0!3m2!1sen!2s!4v1707191923456!5m2!1sen!2s" 
                    width="100%" 
                    height="500" 
                    style="border:0;" 
                    allowfullscreen="" 
                    loading="lazy" 
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
                
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Contact Section End -->
<?php include "footer.php" ?>