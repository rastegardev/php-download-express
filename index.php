<?php

if (isset($_GET['url'])) {

    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');

    function sendProgress($percentage) {
        echo "data: " . json_encode(['percentage' => $percentage]) . "\n\n";
        ob_flush();
        flush();
    }

    function getFilenameFromUrl($url) {
        $parsedUrl = parse_url($url);
        $path = $parsedUrl['path'];
        return basename($path);
    }

    function downloadFile($url, $path) {
        $fp = fopen($path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, 'progress');
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }

    function progress($resource, $download_size, $downloaded, $upload_size, $uploaded) {
        if ($download_size > 0) {
            sendProgress(round($downloaded / $download_size * 100, 2));
        }
    }

    $url = $_GET['url'];
    $filename = getFilenameFromUrl($url);
    downloadFile($url, $filename);
    exit;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>پی‌اچ‌پی دانلود اکسپرس</title>
    <link rel="icon" href="https://rastegar.info/wp-content/uploads/2023/05/cropped-Hologram.png">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;700&display=swap");

        body {
            height: 90vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            font-family: "Vazirmatn", sans-serif;
        }

        .container {
            width: 100%;
            margin: 20px;
            padding: 20px;
            max-width: 1024px;
            display: flex;
            align-items: center;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        #logo {
            width: 150px;
        }

        #downloadForm {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        #progressContainer {
            width: 100%;
            text-align: center;
            border-radius: 50px;
            margin-bottom: 30px;
            background-color: #ddd;
        }

        #progressBar {
            width: 0%;
            height: 30px;
            color: white;
            line-height: 30px;
            border-radius: 50px;
            background-color: #ff0000;
        }

        #message {
            display: none;
            text-align: center;
        }

        .footer-shape {
            width: 100%;
            height: 2px;
            margin-bottom: 20px;
        }

        p {
            font-size: 12px;
            text-align: center;
        }

        input {
            width: 60%;
        }

        input,
        button {
            padding: 10px 20px;
            margin: 5px;
            border-radius: 50px;
            border: 1px solid #ddd;
            font-family: "Vazirmatn", sans-serif;
        }

        button {
            color: white;
            cursor: pointer;
            background-color: #4caf50;
            font-family: "Vazirmatn", sans-serif;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>

<body>
    <div class="container">
        <img src="https://rastegar.info/wp-content/uploads/PHP-logo.png" id="logo" alt="php logo">
        <h1>پی‌اچ‌پی دانلود اکسپرس</h1>
        <form id="downloadForm">
            <input type="text" id="downloadLink" placeholder="لینک دانلود را وارد کنید">
            <button type="submit">دانلود کن</button>
        </form>
        <div id="progressContainer">
            <div id="progressBar">0%</div>
        </div>
        <h2 id="message"></h2>
        <img src="https://rastegar.info/wp-content/uploads/2023/05/Footer-Shape.png" class="footer-shape" alt="Shape">
        <p>طراحی و توسعه توسط <a href="https://rastegar.info/php-download-express/" target="_blank">رضا رستگار</a></p>

    </div>

    <script>
        document.getElementById("progressContainer").style.display = 'none';
        document.getElementById('downloadForm').addEventListener('submit', function (e) {
            e.preventDefault();
            document.getElementById("progressContainer").style.display = 'block';
            let url = document.getElementById('downloadLink').value;
            document.getElementById('downloadLink').value = '';
            let source = new EventSource("index.php?url=" + encodeURIComponent(url));


            source.onmessage = function (event) {
                let data = JSON.parse(event.data);

                if (data.percentage < 100) {
                    document.getElementById("progressBar").style.width = data.percentage + "%";
                    document.getElementById("progressBar").innerHTML = data.percentage + "%";
                } else {
                    document.getElementById("message").style.display = 'block';
                    document.getElementById("message").innerHTML = "فایل با موفقیت دانلود شد";
                    setTimeout(function () {
                        document.getElementById("progressBar").innerHTML = "0%";
                        document.getElementById("progressBar").style.width = "0%";
                        document.getElementById("progressContainer").style.display = 'none';
                        document.getElementById("message").style.display = 'none';
                    }, 5000);

                    source.close();
                }
            };

            source.onerror = function (event) {
                source.close();
            };
        });
    </script>
</body>

</html>