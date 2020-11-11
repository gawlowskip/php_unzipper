<?php

require "src/DirectoryScanner.php";
require "src/FileUnzipper.php";
require "src/Response.php";

use app\src\DirectoryScanner;
use app\src\FileUnzipper;
use app\src\Response;

$dirPath = dirname(__FILE__) . "/public";
$directory = new DirectoryScanner($dirPath);

if (isset($_POST['intervalRefresh'])) {
    $response = $directory->findFiles();
    Response::render($response);
    return;
}

if (isset($_POST['btnUnzip'])) {
    $fileUnzipper = new FileUnzipper();

    $fileName = isset($_POST['zipFile']) ? strip_tags($_POST['zipFile']) : '';
    $filePath = "{$directory->getDirPath()}/{$fileName}";

    $response = $fileUnzipper::unzip($filePath, $directory->getDirPath());
    Response::render($response);
    return;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Unzipper</title>
</head>
<body>

<div>
    <main>

        <select id="zipFile"></select>

        <button type="submit" id="btnUnzip">
            Unzip
        </button>

    </main>
</div>
<script>
    const btnUnzip = document.querySelector('button#btnUnzip');

    let request = obj => {
        return new Promise((resolve, reject) => {
            let xhr = new XMLHttpRequest();

            let urlEncodedData = "",
                urlEncodedDataPairs = [],
                name;

            // Turn the data object into an array of URL-encoded key/value pairs.
            for (name in obj.body) {
                urlEncodedDataPairs.push(encodeURIComponent(name) + '=' + encodeURIComponent(obj.body[name]));
            }

            // Combine the pairs into a single string and replace all %-encoded spaces to
            // the '+' character; matches the behaviour of browser form submissions.
            urlEncodedData = urlEncodedDataPairs.join('&').replace(/%20/g, '+');

            xhr.open(obj.method || "GET", obj.url);

            if (obj.method === 'POST') {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            }

            if (obj.headers) {
                Object.keys(obj.headers).forEach(key => {
                    xhr.setRequestHeader(key, obj.headers[key]);
                });
            }
            xhr.onload = () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    resolve(xhr.response);
                } else {
                    reject(xhr.statusText);
                }
            };
            xhr.onerror = () => reject(xhr.statusText);
            console.log(obj);
            console.log(urlEncodedData);
            xhr.send(urlEncodedData);
        });
    };

    unzip = () => {
        let data = {
            method: "POST",
            url: "",
            body: {
                btnUnzip: '1',
                zipFile: document.getElementById('zipFile').value
            }
        }

        request(data).then(data => {
            console.log(data)
        }).catch(error => {
            console.log(error);
        });
    }

    refresh = () => {
        let data = {
            method: "POST",
            url: "",
            body: {
                intervalRefresh: '1'
            }
        }

        let selectZipFile = document.getElementById('zipFile')

        request(data).then(data => {
            data = JSON.parse(data)

            if (data.data && !data.data.length) {
                selectZipFile.innerHTML = ``;
                return true
            }

            const options = data.data.map(car => {
                const value = car.toLowerCase();
                return `<option value="${value}">${car}</option>`;
            });

            selectZipFile.innerHTML = options;
        }).catch(error => {
            console.log(error);
        });
    }

    btnUnzip.addEventListener('click', () => {
        unzip();
    })

    setInterval(() => {
        refresh();
    }, 1000)
</script>
</body>
</html>

