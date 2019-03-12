<?php include "header.php";?>
  <style>
    body {
    background:#333;
}
.form_bg {
    background-color:#eee;
    color:#666;
    padding:20px;
    border-radius:10px;
    position: absolute;
    border:1px solid #fff;
    margin: auto;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    width: 320px;
    height: 280px;
}
.align-center {
    text-align:center;
}
      </style>
  <body>

    <div class="container">
    <div class="row">
        <div class="form_bg">
            <form>
                 <h2 class="text-center">Login Page</h2>
                <br/>
                <div class="form-group">
                    <input type="email" class="form-control" id="userid" placeholder="User id">
                </div>
                <div class="form-group">
                    <input type="password" class="form-control" id="pwd" placeholder="Password">
                </div>
                <br/>
                <div class="align-center">
                    <button type="submit" class="btn btn-default" id="login">Login</button>
                </div>
            </form>
        </div>
    </div>
</div>


    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
