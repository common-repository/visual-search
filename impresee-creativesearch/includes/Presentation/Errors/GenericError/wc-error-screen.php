<!DOCTYPE html>
<html>
<head>
  <title>We're sorry, but something went wrong (500)</title>
  <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700&display=swap" rel="stylesheet">
  <style type="text/css">
   body {
     background-color: white;
     font-size: 14px;
     font-family: 'Roboto', sans-serif;
     height: 100vh;
     overflow-y: hidden;
   }
  div.dialog {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
  }
  .dialog p { color: #4D4D4D; font-weight:lighter; }
  h2 { color: black; line-height: 1.5em; }
  .impresee-next-button {
    background-color: #2D2662;
    color: white;
    width: 7em;
    cursor: pointer;
    text-align: center;
    font-size: 1.4em;
    height: 2em;
    line-height: 2em;
    text-decoration: none;
    display: inline-block;
  }
  </style>
</head>

<body>
  <!-- This file lives in public/500.html -->
  <div class="dialog">
    <img src="<?php echo $error_image; ?>">
    <h2>Ups! This is embarrassing</h2>
    <p>Please contact us, and we'll make sure to fix this as soon as we can</p>
    <a class="impresee-next-button" href="mailto:support@impresee.com">Contact us</a>
  </div>
</body>
</html>
