<?php
/*
Plugin Name: 3D Printing Process
Description: A web application for 3D printing processes with multiple steps in a single page.
Version: 1.0
Author: Your Name
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Register shortcode to display the application
function printing_process_shortcode() {
    ob_start();
    ?>
    <div id="printing-process-container">
        <div id="step-1" class="step">
            <h1>Welcome to 3D Printing Process</h1>
            <button class="next-button">Start</button>
        </div>

        <div id="step-2" class="step">
            <div class="status-bar">
                <div class="step active">Process</div>
                <div class="step">Inputs</div>
                <div class="step">Result</div>
                <div class="step">Request</div>
            </div>
            <h2>Choose the Printing Process</h2>
            <form id="process-form">
                <label>
                    <input type="radio" name="process" value="FFF"> FFF
                </label>
                <label>
                    <input type="radio" name="process" value="SLS"> SLS
                </label>
                <label>
                    <input type="radio" name="process" value="SLM"> SLM
                </label>
                <label>
                    <input type="radio" name="process" value="Poly Jet"> Poly Jet
                </label>
                <label>
                    <input type="radio" name="process" value="Pulverdüse"> Pulverdüse
                </label>
            </form>
            <div class="navigation">
                <button class="back-button" style="display:none;">&larr; Back</button>
                <button class="next-button">&rarr; Next</button>
            </div>
        </div>

        <div id="step-3" class="step">
            <div class="status-bar">
                <div class="step">Process</div>
                <div class="step active">Inputs</div>
                <div class="step">Result</div>
                <div class="step">Request</div>
            </div>
            <h2>Input Parameters</h2>
            <form id="parameter-form">
                <label>Height (mm): <input type="number" name="height" id="height"></label>
                <label>Cross Section (mm²): <input type="number" name="cross-section" id="cross-section"></label>
                <label>Material: <select name="material" id="material">
                    <!-- Materials will be populated dynamically -->
                </select></label>
                <button class="next-button">Calculate</button>
            </form>
            <div class="navigation">
                <button class="back-button">&larr; Back</button>
            </div>
        </div>

        <div id="step-4" class="step">
            <div class="status-bar">
                <div class="step">Process</div>
                <div class="step">Inputs</div>
                <div class="step active">Result</div>
                <div class="step">Request</div>
            </div>
            <h2>Calculation Results</h2>
            <div id="user-input-summary">
                <p>Chosen Process: <span id="chosen-process"></span></p>
                <p>Height: <span id="summary-height"></span> mm</p>
                <p>Cross Section: <span id="summary-cross-section"></span> mm²</p>
                <p>Material: <span id="summary-material"></span></p>
            </div>
            <h3>Price Table</h3>
            <table id="price-table">
                <thead>
                    <tr>
                        <th>Process</th>
                        <th>Price (€)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Results will be populated dynamically -->
                </tbody>
            </table>
            <button class="next-button">Request</button>
            <div class="navigation">
                <button class="back-button">&larr; Back</button>
            </div>
        </div>

        <div id="step-5" class="step">
            <div class="status-bar">
                <div class="step">Process</div>
                <div class="step">Inputs</div>
                <div class="step">Result</div>
                <div class="step active">Request</div>
            </div>
            <h2>Request Summary</h2>
            <div id="request-summary">
                <p>Chosen Process: <span id="final-process"></span></p>
                <p>Height: <span id="final-height"></span> mm</p>
                <p>Cross Section: <span id="final-cross-section"></span> mm²</p>
                <p>Material: <span id="final-material"></span></p>
                <p>Price: <span id="final-price"></span> €</p>
            </div>
            <form id="request-form">
                <label>First Name: <input type="text" name="first-name"></label>
                <label>Last Name: <input type="text" name="last-name"></label>
                <label>Email: <input type="email" name="email"></label>
                <div id="file-upload">
                    <input type="file" name="file-upload" accept="application/pdf,image/png" style="display:none;" id="file-input">
                    <p class="choose-file-button">Choose file</p>
                    <div id="file-info" style="display:none;">
                        <span id="uploaded-file-name"></span>
                        <button type="button" id="remove-file" style="color: red;">X</button>
                    </div>
                </div>

                <button class="next-button" type="button" id="send-request">Send Request</button>
            </form>
            <div class="navigation">
                <button class="back-button">&larr; Back</button>
            </div>
        </div>

        <div id="step-6" class="step">
            <h2>Request Submitted</h2>
            <p>Your request has been successfully sent.</p>
            <button onclick="window.location.href='https://www.fh-swf.de/de/forschung___transfer_4/labore_3/labs/3d_druckzentrum/index.php'">Go to Website</button>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( '3d_printing_process', 'printing_process_shortcode' );

// Enqueue necessary scripts and styles
function enqueue_printing_process_assets() {
    wp_enqueue_style( 'printing-process-style', plugins_url( '/assets/css/style.css', __FILE__ ) );
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'printing-process-script', plugins_url( '/assets/js/script.js', __FILE__ ), array('jquery'), null, true );
    wp_localize_script( 'printing-process-script', 'ajaxurl', admin_url( 'admin-ajax.php' ) );  // To use ajaxurl in JavaScript
}
add_action( 'wp_enqueue_scripts', 'enqueue_printing_process_assets' );


// Function to send emails
function send_request_emails($user_inputs, $file_path) {
    $user_email = sanitize_email($user_inputs['email']);
    $user_first_name = sanitize_text_field($user_inputs['first-name']);
    $user_last_name = sanitize_text_field($user_inputs['last-name']);
    $process = sanitize_text_field($user_inputs['process']);
    $price = sanitize_text_field($user_inputs['price']);
    $height = sanitize_text_field($user_inputs['height']);
    $cross_section = sanitize_text_field($user_inputs['cross-section']);
    $material = sanitize_text_field($user_inputs['material']);

    // Email to user
    $subject_user = 'Thank you for your 3D printing request';
    $message_user = "Dear $user_first_name $user_last_name,\n\n";
    $message_user .= "Thank you for your request.\n\n";
    $message_user .= "Your request summary is:\n";
    $message_user .= "Process: $process\nHeight: $height mm\nCross Section: $cross_section mm²\nMaterial: $material\nPrice: €$price\n\n";
    $message_user .= "We will process your request and get back to you shortly.\n\nBest regards, 3D Printing Team";

    $headers_user = array('Content-Type: text/plain; charset=UTF-8');
    wp_mail($user_email, $subject_user, $message_user, $headers_user);

    // Email to admin
    $subject_admin = 'New 3D Printing Request';
    $message_admin = "New 3D Printing Request from $user_first_name $user_last_name\n\n";
    $message_admin .= "First Name: $user_first_name\nLast Name: $user_last_name\nEmail: $user_email\n\n";
    $message_admin .= "Request Summary:\n";
    $message_admin .= "Process: $process\nHeight: $height mm\nCross Section: $cross_section mm²\nMaterial: $material\nPrice: €$price\n\n";
    $message_admin .= "File attached: $file_path";

    $headers_admin = array('Content-Type: text/plain; charset=UTF-8');
    wp_mail('thithuylinh.le1@gmail.com', $subject_admin, $message_admin, $headers_admin);
}

// Handling request submission
add_action('wp_ajax_send_request', 'send_request_callback');
add_action('wp_ajax_nopriv_send_request', 'send_request_callback'); // For non-logged-in users

function send_request_callback() {
    if (isset($_POST['user_inputs']) && isset($_FILES['file'])) {
        $user_inputs = $_POST['user_inputs'];
        $uploaded_file = $_FILES['file'];

        // Save the uploaded file to the server
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . basename($uploaded_file['name']);
        move_uploaded_file($uploaded_file['tmp_name'], $file_path);

        // Send emails
        send_request_emails($user_inputs, $file_path);

        // Send response back
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}

?>
