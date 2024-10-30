<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API CONSULIFE</title>
</head>

<body>
    <style>
        .responsive-container {
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .responsive-container img {
            height: 95vh;
            max-width: 100%;
            object-fit: cover;
        }

        .text-content {
            margin-left: 20px;
            max-width: 100%;
        }

        @media (max-width: 768px) {
            .responsive-container {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .responsive-container img {
                height: auto;
                width: 100%;
                margin-bottom: 20px;
            }

            .text-content {
                margin-left: 0;
            }
        }
    </style>

    <div class="responsive-container">
        <img src="images/images.jpeg" alt="Responsive Image">
        <div class="text-content">
            <h1>MAU NGAPAIN BANG?</h1>
            <h2>LAGI MODE DEVELOP</h2>
            <h3>BACKEND NYA GANTENG BANGET</h3>
            <h3>
                Ikan hiu di Laut Jawa
                <br>
                I love you sepenuh jiwa ❤️.
            </h3>
            <h3>HIHIHI 😍</h3>
            <hr>
            <a href="/api-documentation">API Documentation</a>
        </div>
    </div>
</body>

</html>