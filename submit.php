<?php
// import secret keys and email
include_once 'keys.php';

// Declare default values
$postData = $valErr = $statusMsg = '';
$status = 'error';


//Form checking
if(isset($_POST['submit_frm'])){
    // get post data
    $postData = $_POST;
    $name = $_POST['name'];
    $company = $_POST['company'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $message = $_POST['message'];

    // Validate data
    // First step check that required fields are present and sanitize input
    (empty($name)) ? $valErr = $valErr . "Name is Required " :  $name = htmlspecialchars($name);
    (empty($company)) ? $valErr = $valErr . "Comapny is Required " :  $company = htmlspecialchars($company);
    (empty($email)) ? $valErr = $valErr . "Email is Required " :  $email = $email;
    (empty($phone)) ? $valErr = $valErr . "Phone is Required " :  $phone = htmlspecialchars($phone);
    $message = htmlspecialchars($message);

    // check input using regular experssions
    if(!preg_match("/^[a-zA-Z-' ]*$/",$name)){
        $valErr = $valErr . "Invalid Name ";
    }

    if(!preg_match("/^[a-zA-Z-' ]*$/",$company)){
        $valErr = $valErr . "Invalid Company Name ";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $valErr = $valErr . "Invalid email format ";
    }

    // Check if submitted data is valid
    if(empty($valErr)){
        // validate recaptcha response
        if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])){
            // verify recaptcha response
            $api_url = 'https://www.google.com/recaptcha/api/siteverify';
            $resq_data = array(
                'secret' => $secretKey,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            );

            $curlConfig = array( 
                CURLOPT_URL => $api_url, 
                CURLOPT_POST => true, 
                CURLOPT_RETURNTRANSFER => true, 
                CURLOPT_POSTFIELDS => $resq_data, 
                CURLOPT_SSL_VERIFYPEER => false 
            );

            $ch = curl_init(); 
            curl_setopt_array($ch, $curlConfig); 
            $response = curl_exec($ch); 
            if (curl_errno($ch)) { 
                $api_error = curl_error($ch); 
            } 
            curl_close($ch); 

            // Decode JSON data of API response in array 
            $responseData = json_decode($response);

            if(!empty($responseData) && $responseData->success){ 
                // Send email notification to the site admin 
                $to = $recipientEmail; 
                $subject = 'New Contact Request Submitted'; 
                $htmlContent = " 
                    <h4>Contact request details</h4> 
                    <p><b>Name: </b>".$name."</p> 
                    <p><b>Company: </b>".$company."</p> 
                    <p><b>Email: </b>".$email."</p> 
                    <p><b>Phone: </b>".$phone."</p> 
                    <p><b>Message: </b>".$message."</p> 
                "; 
                 
                // Always set content-type when sending HTML email 
                $headers = "MIME-Version: 1.0" . "\r\n"; 
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
                // Sender info header 
                $headers .= 'From:'.$name.' <'.$email.'>' . "\r\n"; 
                 
                // Send email 
                @mail($to, $subject, $htmlContent, $headers); 
                 
                $status = 'success'; 
                $statusMsg = 'Thank you! Your contact request has been submitted successfully.'; 
                $postData = ''; 
            }else{ 
                $statusMsg = !empty($api_error)?$api_error:'The reCAPTCHA verification failed, please try again.'; 
                $status = 'recaptchaError';
            } 
        } else {
            $statusMsg = 'Something went wrong, please try again.'; 
            $status = 'recaptchaError';
        }
    }else{
        $status = 'inputError';
    }
}
?>

<!-- Create message display -->

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]>      <html class="no-js"> <!--<![endif]-->
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Message Submission</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="css/styles.css">
        <!-- Recaptcha -->
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <script>
            function onSubmit(token) {
                document.getElementById("contactForm").submit();
            }
        </script>
    </head>
    <body id="page-top">
        <!--[if lt IE 7]>
            <p class="browsehappy">You are using an <strong>outdated</strong> browser. Please <a href="#">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->

        <!-- Navigation Bar -->

        <nav class="navbar navbar-expand-lg  text-uppercase fixed-top navbar-colors" id="mainNav">
            <div class="container">
                <a class="navbar-brand text-white" href="#page-top">Home</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 text-white rounded" href="index.html#portfolio"><span data-bs-target="#navbarResponsive" data-bs-toggle="collapse">Portfolio</span></a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 text-white rounded" href="index.html#about"><span data-bs-target="#navbarResponsive" data-bs-toggle="collapse">About</span></a></li>
                        <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 text-white rounded" href="index.html#contact"><span data-bs-target="#navbarResponsive" data-bs-toggle="collapse">Contact</span></a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navigation -->

        <!-- Header -->

        <header class="text-center text-white header-colors">
            <div class="container d-flex align-items-center flex-column">
                <?php
                switch($status){
                    case ('success'):
                        echo '
                        <h1 class="display-2 text-uppercase mb-0">Submission Successful</h1>
                        <hr class="hr-divider-light">
                        <p class="h3 mb-0">'. $statusMsg .'</p>
                        <p class="h3 mb-0">Will Redirect in 3 Seconds</p>
                        ';
                        header("Refresh:3; url=index.html");
                        break;
                    case('recaptchaError'):
                        echo '
                        <h1 class="display-2 text-uppercase mb-0">Submission Failed</h1>
                        <hr class="hr-divider-light">
                        <p class="h3 mb-0">'. $statusMsg .'</p>
                        <p class="h3 mb-0">Will Redirect in 5 Seconds</p>
                        ';
                        header("Refresh:5; url=index.html#contact");
                        break;
                    case('inputError'):
                        echo '
                        <h1 class="display-2 text-uppercase mb-0">Submission Failed</h1>
                        <hr class="hr-divider-light">
                        <p class="h3 mb-0">'. $valErr .'</p>
                        <p class="h3 mb-0">Will Redirect in 5 Seconds</p>
                        ';
                        header("Refresh:5; url=index.html#contact");
                        break;
                    default:
                        echo '
                        <h1 class="display-2 text-uppercase mb-0">Submission Failed</h1>
                        <hr class="hr-divider-light">
                        <p class="h3 mb-0">Something went wrong</p>
                        <p class="h3 mb-0">Will Redirect in 5 Seconds</p>
                        ';
                        header("Refresh:5; url=index.html#contact");
                        break;
                }
                ?>
            </div>
        </header>
        <!-- End Header -->

        <!-- Footer-->
        <footer class="footer text-center">
            <div class="container">
                <div class="row">
                    <!-- Footer Location-->
                    <div class="col-lg-4 mb-5 mb-lg-0">
                        <h4 class="text-uppercase mb-4">Location</h4>
                        <p class="lead mb-0">
                            Lindenhurst, IL
                        </p>
                    </div>
                    <!-- Footer Social Icons-->
                    <div class="col-lg-4 mb-5 mb-lg-0">
                        <h4 class="text-uppercase mb-4">Around the Web</h4>
                        <a href="https://www.github.com/jlenz126" target="_blank"><img class="mb-5" src="assets/img/github.png" alt="github logo"></a>
                        <a href="https://www.linkedin.com/in/jacob-lenz-401602230" target="_blank"><img class="mb-5" src="assets/img/linkedin.png" alt="linkedin logo"></a>
                    </div>
                    <!-- Footer About Text-->
                    <div class="col-lg-4">
                        <h4 class="text-uppercase mb-4">About Freelancer</h4>
                        <p class="lead mb-0">
                            Parts of this site are inspired by Freelance which is a free to use, MIT licensed Bootstrap theme created by
                            <a href="http://startbootstrap.com" class="text-white">Start Bootstrap</a>
                            .
                        </p>
                    </div>
                </div>
            </div>
        </footer>
        <!-- Copyright Section-->
        <div class="copyright py-4 text-center text-white">
            <div class="container"><small>Copyright &copy; Jacob Lenz 2023</small></div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    </body>
</html>