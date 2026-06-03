<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Không Tìm Thấy Trang</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #050505;
            overflow: hidden;
        }

        .rgb-card {
            position: relative;
            width: 450px;
            height: 300px;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 20px;
            overflow: hidden;
        }

        .rgb-card::before {
            content: '';
            position: absolute;
            width: 150px;
            height: 200%;
            background: linear-gradient(#00ccff, #d400d4);
            animation: animateRGB 4s linear infinite;
        }

        .rgb-card::after {
            content: '';
            position: absolute;
            inset: 5px;
            background: #0f0f0f;
            border-radius: 16px;
        }

        @keyframes animateRGB {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .content {
            position: relative;
            z-index: 10;
            text-align: center;
            width: 100%;
            padding: 20px;
        }

        h2 {
            color: #ff4d4d;
            font-size: 1.8em;
            letter-spacing: 2px;
            text-shadow: 0 0 10px #ff0000, 0 0 20px #ff0000;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        p {
            color: #aaa;
            font-size: 1.05em;
            margin-bottom: 10px;
        }

        #countdown {
            color: #d400d4;
            font-weight: bold;
            font-size: 1.4em;
            text-shadow: 0 0 10px #d400d4;
        }

        .progress-bar {
            width: 80%;
            height: 6px;
            background: #222;
            border-radius: 10px;
            margin: 15px auto;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #ff0000, #ffff00, #00ff00, #00ffff, #0000ff, #ff00ff, #ff0000);
            background-size: 200%;
            animation: shrink 5s linear forwards, rgbFlow 2s linear infinite;
        }

        @keyframes shrink {
            0% { width: 100%; }
            100% { width: 0%; }
        }

        @keyframes rgbFlow {
            0% { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

        .home-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            border-radius: 25px;
            background: linear-gradient(45deg, #00ccff, #d400d4);
            color: #fff;
            text-decoration: none;
            font-size: 0.95em;
            transition: 0.3s;
        }

        .home-btn:hover {
            opacity: 0.8;
        }
    </style>
</head>
<body>

    <div class="rgb-card">
        <div class="content">
            <h2>404 - Sai Link</h2>
            <p>Bạn đã truy cập sai đường dẫn hoặc trang không tồn tại.</p>
            <p>Vui lòng quay lại trang chủ sau <span id="countdown">5</span>s</p>

            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>

            <!-- Nút quay về trang chủ -->
            <a href="/" class="home-btn">Quay về trang chủ</a>
        </div>
    </div>

    <script>
        let timeLeft = 10;
        const countdownEl = document.getElementById('countdown');

        const timer = setInterval(() => {
            timeLeft--;
            countdownEl.innerText = timeLeft;

            if (timeLeft <= 0) {
                clearInterval(timer);
                window.location.href = "/";
            }
        }, 1000);
    </script>

</body>
</html>