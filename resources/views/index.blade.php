<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="stylesheet" href="/index.css" />
    <!--[if lte IE 10]>
    <script
            src="https://as.alipayobjects.com/g/component/??console-polyfill/0.2.2/index.js,media-match/2.0.2/media.match.min.js"></script>
    <![endif]-->

    <style>
        @-webkit-keyframes spin {
            0% {
                transform: rotate(0deg)
            }

            to {
                transform: rotate(1turn)
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg)
            }

            to {
                transform: rotate(1turn)
            }
        }

        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1000;
        }

        #preloader > div {
            display: block;
            position: relative;
            left: 50%;
            top: 50%;
            width: 150px;
            height: 150px;
            margin: -75px 0 0 -75px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #f95372;
            transform: translateZ(0);
            animation: spin 2s linear infinite
        }

        #preloader > div:before {
            content: "";
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #00abff;
            -webkit-animation: spin 3s linear infinite;
            animation: spin 3s linear infinite
        }

        #preloader > div:after {
            content: "";
            position: absolute;
            top: 15px;
            left: 15px;
            right: 15px;
            bottom: 15px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-top-color: #e7ba08;
            animation: spin 1.5s linear infinite
        }
    </style>
</head>

<body>
<div id="root" style="position: relative;height: 100%">
    <div id="preloader">
        <div>
        </div>
    </div>
</div>
<script src="/index.js"></script>
</body>

</html>
