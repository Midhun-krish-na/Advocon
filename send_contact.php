<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Advocon</title>
    <link rel="stylesheet" href="css/send_contact.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" />
</head>
<body>
    <header>
        <nav class="navbar">
            <img src="img/icon/logoadvocon.png" alt="logo" class="logo">
            <ul class="nav-list">
            <li>
                <a href="javascript:history.back()" class="back-button" style="color: #2d3844; text-decoration: none; padding: 10px; background-color: #6781ff; border-radius: 5px;"
                onmouseover="this.style.backgroundColor='#0080ff';" 
                onmouseout="this.style.backgroundColor='#6781ff';">
                Back
                </a>
            </li>
                <li><a class="ltn" href="login.php">Login</a></li>
            </ul>
        </nav>
    </header>

    <section class="contact-us">
        <div class="container">
            <h1>Contact Admin</h1>
            <p>If you have any questions or concerns, feel free to contact the admin using the form below. We aim to respond to all inquiries within 24-48 hours.</p>
            
            <form action="send_contact.php" method="POST" class="contact-form">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" placeholder="Enter subject of your message" required>
                </div>

                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5" placeholder="Write your message here" required></textarea>
                </div>

                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>
    </section>

    <!-- Footer -->
    <section class="footer">
        <div class="grid-wrapper">
            <div class="col-4 footer1">
                <h1>Advocon.</h1>
                <p>Your trusted partner for legal assistance.</p>
                <div class="social-prof">
                    <i class="fa fa-facebook"></i>
                    <i class="fa fa-instagram"></i>
                </div>
            </div>
            <div class="col-4 footer3">
                <h1>Subscribe</h1>
                <p>Subscribe to our newsletter for the latest updates and news.</p>
                <input type="text" placeholder="Enter your email">
                <button class="subscribe-btn">Subscribe</button>
            </div>
        </div>
        <hr>
        <p class="footer-p">©2024 All Rights Reserved. Advocon.</p>
    </section>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="js/main.js"></script>
</body>
</html>
