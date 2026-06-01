<?php
session_start();
// require_once("includes/db.php");
require_once("db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile - CAReva</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body{
            background:#050816;
            color:white;
            font-family:Arial,sans-serif;
        }

        .profile-container{
            width:80%;
            max-width:800px;
            margin:50px auto;
            background:rgba(255,255,255,0.05);
            border:1px solid #00e5ff;
            border-radius:20px;
            padding:30px;
            box-shadow:0 0 20px #00e5ff;
        }

        .profile-title{
            text-align:center;
            color:#00e5ff;
            margin-bottom:30px;
        }

        .profile-row{
            margin:15px 0;
            padding:12px;
            background:rgba(255,255,255,0.03);
            border-radius:10px;
        }

        .profile-row strong{
            color:#00e5ff;
        }

        .btn{
            display:inline-block;
            margin-top:20px;
            padding:12px 20px;
            background:#00e5ff;
            color:black;
            text-decoration:none;
            border-radius:10px;
            font-weight:bold;
        }

        .btn:hover{
            transform:scale(1.05);
        }
    </style>
</head>
<body>

<div class="profile-container">

    <h1 class="profile-title">👤 My Profile</h1>

    <div class="profile-row">
        <strong>Full Name:</strong>
        <?php echo $user['full_name']; ?>
    </div>

    <div class="profile-row">
        <strong>Email:</strong>
        <?php echo $user['email']; ?>
    </div>

    <div class="profile-row">
        <strong>Mobile:</strong>
        <?php echo $user['mobile']; ?>
    </div>

    <div class="profile-row">
        <strong>Date of Birth:</strong>
        <?php echo $user['dob']; ?>
    </div>

    <div class="profile-row">
        <strong>Gender:</strong>
        <?php echo $user['gender']; ?>
    </div>

    <div class="profile-row">
        <strong>City:</strong>
        <?php echo $user['city']; ?>
    </div>

    <div class="profile-row">
        <strong>State:</strong>
        <?php echo $user['state']; ?>
    </div>

    <a href="dashboard.php" class="btn">⬅ Back to Dashboard</a>

</div>

</body>
</html>