<!doctype html>
<html lang="en">
  <head>
  	<title>Admin LogIn</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="../css/style.css">
	<link rel="shortcut icon" href="../img/favicon.png" type="image/x-icon">

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
		



	<div class="container">
		<div class="h-screen flex items-center justify-center backimg">
			<div
			  class="bg-green bg-opacity-0 backdrop-blur-lg px-8 py-10 rounded-md border"
			>
			<div class="logo flex flex-col items-center justify-center h-10 w-full text-center ">
				<a href="index.php"><img src="image/favicon.png" alt="Logo" style="width: 80px;"></a>
			</div>
			<h2 class="text-4xl text-center mb-5 font-semibold text-green-500 py-3">Admin Login</h2>
			
            <form method="POST" action="Aloginhelper.php">
                
                <input type="text" class="text-white" name ="e" style="background:transparent; outline:none;" placeholder="Email">

				<br><br>
                <input type="password" class="text-white" name="p" style="background:transparent; outline:none;" placeholder="Password">

                <br><br>
                <div class="w-full flex justify-center items-center" ><button type="submit"  class="focus:outline-none text-white bg-[#22c55e]    text-sm px-5 py-2.5 me-2 mb-2 dark:bg-green-600 dark:hover:bg-green-700 rounded">Log In</button></div>
            </form>
				
			  
			  
			</div>
		  </div>
	</div>

	<script src="js/jquery.min.js"></script>
  <script src="js/popper.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>

	</body>
</html>

