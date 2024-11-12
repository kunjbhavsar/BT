<?php
session_start();
ob_start();

$csvFilePath = "Transit_Stops_and_Routes_-4623178264090592728.csv";

$destinations = [];

if (($csvFile = fopen($csvFilePath, "r")) !== FALSE) {
    while (($row = fgetcsv($csvFile)) !== FALSE) {

        $destinations[] = $row[3]; 
    }
    fclose($csvFile);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brampton Transit Car</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
        var destinations = <?php echo json_encode($destinations); ?>;

        $("#destination").autocomplete({
            source: function(request, response) {
                var searchTerm = request.term.toLowerCase();

                var matches = destinations.filter(function(destination) {
                    return destination.toLowerCase().startsWith(searchTerm);
                });

                response(matches); 
            },
                minLength: 1 
            });
        });
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #003366;
            color: #ffffff;
        }
        .header, .footer {
            background-color: #0066cc;
            color: #ffffff;
            padding: 15px 0;
            text-align: center;
        }
        .content {
            padding: 20px;
            color: #ffffff;
            max-width: 500px;
            margin: 20px auto;
        }
        
        h1, h2 {
            color: #ffffff;
        }
        .btn-primary {
            background-color: #0066cc;
            border: none;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #005bb5;
        }
        .text-success, .text-warning, .text-danger {
            font-size: 1.1em;
            margin-top: 15px;
        }
        #timer {
            font-size: 1.5em;
            color: #cc0000;
        }

        
        .bus-animation-wrapper {
            position: relative;
            white-space: nowrap;
            width: 100%;
            overflow: hidden;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        .bus-image {
            width: 300px;
            height: auto;
            margin-right: 15px;
            vertical-align: middle;
            animation: moveBus 7s linear infinite;
        }

        .bus-text {
            display: inline-block;
            font-size: 1.0em;
        }

        
        @keyframes moveBus {
            0% {
                transform: translateX(100%); /* Start from the right */
            }
            100% {
                transform: translateX(-100%); /* Move to the left */
            }
        }
    </style>
    <script>
        function startTimer(duration, display) {
            var timer = duration, minutes, seconds;
            var countdown = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(countdown);
                    alert("5 minutes have passed.");
                    location.reload();
                }
            }, 1000);
        }

        window.onload = function () {
            var fiveMinutes = 60 * 5,
                display = document.querySelector('#timer');
            startTimer(fiveMinutes, display);
        };
    </script>
</head>
<body>
    <div class="header">
        <img src="BT logo.jpg" alt="Brampton Transit Logo" style="max-width: 450px; height: auto;">
        <p><h2>Your Ride, Your City</h2></p>
    </div>

    <div class="content">
        <?php
        if (!isset($_SESSION['carpoolQueue'])) {
            $_SESSION['carpoolQueue'] = [];
        }

        function isBusComingSoon() {
            $busArrivalTime = rand(5, 30);
            return $busArrivalTime <= 15;
        }

        function calculateCarpoolPrice($destination) {
            $basePrice = 1.00;
            $distanceFactor = 0.5;
            $price = $basePrice + (strlen($destination) * $distanceFactor);
            if ($price > 20) {
                $price = $price/3;
            }
            if ($price > 10) {
                $price = $price/2;
            }
            if ($price > 6) {
                $price = $price/1.5;
            }
            return number_format($price, 2);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['step']) && $_POST['step'] == '1') {
            if (isBusComingSoon()) {
                echo "<div class='bus-animation'>
                        <img src='BUS IS ON THE WAY.webp' class='bus-image' alt='Bus Image'>
                        <span class='bus-text'><h5>A bus is arriving in less than 15 minutes. We recommend you take the bus.</h5></span>
                      </div>";
            } else {
                echo '<form method="post" action="">';
                echo '<input type="hidden" name="step" value="2">';
                echo '<p>The next bus is more than 15 minutes away. Would you like to:</p>';
                echo '<div class="form-check">';
                echo '<input class="form-check-input" type="radio" name="transport" value="Wait" required> Wait for the Bus.<br>';
                echo '</div>';
                echo '<div class="form-check">';
                echo '<input class="form-check-input" type="radio" name="transport" value="Uber"> Take a Transit Car<br><br>';
                echo '</div>';
                echo '<button type="submit" class="btn btn-primary">Continue</button>';
                echo '</form>';
            }
        } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['step']) && $_POST['step'] == '2') {
            $transport = htmlspecialchars($_POST['transport']);
            if ($transport == "Uber") {
                echo '<form method="post" action="">';
                echo '<input type="hidden" name="step" value="3">';
                echo '<label for="destination">Enter your destination:</label><br>';
                echo '<input type="text" id="destination" name="destination" required class="form-control"><br>';
                echo '<button type="submit" class="btn btn-primary">Submit</button>';
                echo '</form>';
            } else {

                echo "<div class='bus-animation'>
                    <img src='white bt bus.webp' class='bus-image' alt='Bus Image'>
                    <span class='bus-text'><h5>Have a safe and happy journey! Your bus is on the way.</h5></span>
                  </div>";
                echo "<script>
                        setTimeout(function() {
                            window.location.href = 'index.php'; // Redirect to homepage after 5 seconds
                        }, 5000);
                      </script>";
            }
        } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['step']) && $_POST['step'] == '3') {
            $destination = htmlspecialchars($_POST['destination']);
            $price = calculateCarpoolPrice($destination);
            echo "<p>The estimated price for Transit Car to <strong>$destination</strong> is: <strong>$$price</strong></p>";
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="step" value="4">';
            echo '<input type="hidden" name="destination" value="' . htmlspecialchars($destination) . '">';
            echo '<input type="hidden" name="price" value="' . htmlspecialchars($price) . '">';
            echo '<p>Would you like to schedule a Transit Car for this destination?</p>';
            echo '<div class="form-check">';
            echo '<input class="form-check-input" type="radio" name="joinCarpool" value="Yes" required> Yes<br>';
            echo '</div>';
            echo '<div class="form-check">';
            echo '<input class="form-check-input" type="radio" name="joinCarpool" value="No"> No<br><br>';
            echo '</div>';
            echo '<button type="submit" class="btn btn-primary">Confirm</button>';
            echo '</form>';
        } elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['step']) && $_POST['step'] == '4') {
            $joinCarpool = htmlspecialchars($_POST['joinCarpool']);
            if ($joinCarpool == "Yes") {
                $destination = htmlspecialchars($_POST['destination']);
                $price = htmlspecialchars($_POST['price']);
                $_SESSION['carpoolQueue'][] = [
                    "destination" => $destination,
                    "price" => $price,
                    "time" => time(),
                ];
                echo "<p class='text-success'>You have been added to the Transit Car queue for <strong>$destination</strong>.</p>";
                checkCarpoolQueue();
            } else {

                header("Location: index.php");
                exit();
            }
        } else {
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="step" value="1">';
            echo '<button type="submit" class="btn btn-primary">Scan Your PESTRO Check Bus Availability</button>';
            echo '</form>';
        }

        function checkCarpoolQueue() {
            $currentTime = time();
            $waitingUsers = array_filter($_SESSION['carpoolQueue'], function ($user) use ($currentTime) {
                return isset($user['time']) && ($currentTime - $user['time']) <= 300;
            });
            if (count($waitingUsers) >= 2) {
                echo "<p class='text-success'>There are enough people for the carpool. Sending a Transit Car!</p>";
            } elseif (count($waitingUsers) == 1) {
                echo "<p>You're the only one in the queue. Waiting for 5 more minutes...</p>";
                echo '<div id="timer">00:00</div>';
            } else {
                echo "<p class='text-danger'>The carpool queue is empty. Please wait a bit longer.</p>";
            }
        }
        ?>
    </div>

    <div class="footer">
        <p>&copy; Brampton Transit | Your Ride, Your City</p>
    </div>
</body>
</html>

<?php
ob_end_flush(); 
?>