<!doctype html>
<html lang="en">
  <head>
  	<title>Student LogIn</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="css/style.css">
	<link rel="shortcut icon" href="img/favicon.png" type="image/x-icon">

	 <!-- Google Web Fonts -->
	 <link rel="preconnect" href="https://fonts.googleapis.com">
	 <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	 <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
 
	 <!-- Icon Font Stylesheet -->
	 <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
	 <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
 
	 <!-- Libraries Stylesheet -->
	 <link href="lib/animate/animate.min.css" rel="stylesheet">
	 <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
 
	 <!-- Customized Bootstrap Stylesheet -->
	 <link href="css/bootstrap.min.css" rel="stylesheet">
 
	 <!-- Template Stylesheet -->
	 <link href="css/login.css" rel="stylesheet">
     <link rel="stylesheet" href="style.css">
	 <link rel="shortcut icon" href="image/favicon.png" type="image/x-icon">

	 <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.23/dist/full.min.css" rel="stylesheet" type="text/css" />
<script src="https://cdn.tailwindcss.com"></script>



	</head>
	<body class="img js-fullheight" >
		



	<div class="flex items-center justify-center min-h-screen bg-cover bg-center backimg">
    <div class="bg-green-600 bg-opacity-10 backdrop-blur-lg px-8 py-10 rounded-md border border-green-300 shadow-lg w-full max-w-md">
        <div class="logo flex flex-col items-center justify-center h-16 w-full text-center mb-4">
            <a href="index.php"><img src="image/favicon.png" alt="Logo" class="w-20"></a>
        </div>
        <h2 class="text-3xl text-center font-semibold text-green-500 py-3">Student Login</h2>

        <form method="POST" action="Sloginhelper.php" class="space-y-4">
            <input type="email" name="e" class="w-full px-4 py-2 text-white bg-transparent border-b border-green-400 focus:outline-none focus:border-green-600" placeholder="Email" required>

            <input type="password" name="p" class="w-full px-4 py-2 text-white bg-transparent border-b border-green-400 focus:outline-none focus:border-green-600" placeholder="Password" required>

            <div class="w-full flex justify-center">
                <button type="submit" class="w-full text-white bg-green-500 hover:bg-green-600 text-sm px-5 py-2.5 rounded transition">Log In</button>
            </div>
        </form>
    </div>
</div>


	<script src="js/jquery.min.js"></script>
  <script src="js/popper.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>

	</body>
</html>

