<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="theme-color" content="#ffde17">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Already Signed</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #d8dde4;
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
        }

        .signed-card {
            width: 100%;
            max-width: 720px;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.16);
            text-align: center;
            padding: 60px 40px 70px;
        }

        .signed-icon-wrap {
            width: 200px;
            height: 200px;
            margin: 0 auto 28px;
            border-radius: 50%;
            background: #efefeb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signed-icon {
            font-size: 92px;
            line-height: 1;
            color: #f5cc00;
            font-weight: 700;
        }

        .signed-title {
            margin: 0 0 16px;
            font-size: 42px;
            line-height: 1.1;
            color: #f5cc00;
            font-weight: 700;
        }

        .signed-text {
            margin: 0;
            font-size: 18px;
            line-height: 1.5;
            color: #2e4d73;
        }

        @media (max-width: 768px) {
            .signed-card {
                padding: 40px 24px 50px;
            }

            .signed-icon-wrap {
                width: 150px;
                height: 150px;
            }

            .signed-icon {
                font-size: 72px;
            }

            .signed-title {
                font-size: 32px;
            }

            .signed-text {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="signed-card">
        <div class="signed-icon-wrap">
            <div class="signed-icon">!</div>
        </div>

        <h1 class="signed-title">Already Signed</h1>
        <p class="signed-text">This document has been already signed and is no longer accessible.</p>
    </div>
</body>

</html>