<html>
	<head>
		<title>Error</title>
	</head>

	<body>
		<!-- Mix some php and html here. Just because -->
		<!-- This pulls in the name and the age from the form when the user hits the submit button. -->
		<p>
			Message: <?php echo htmlspecialchars($_POST['message']); ?>.
		</p>

	</body>
</html>