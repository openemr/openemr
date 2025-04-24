<?php

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

            $oldUploadDir = __DIR__ . '/public/images/logos/custom/rideon/';
            $newUploadDir = str_replace('/interface/smart/', '/', $oldUploadDir);
            if (!is_dir($newUploadDir)) {
                mkdir($newUploadDir, 0755, true); // create uploads folder if it doesn't exist
            }

            $filename = basename($_FILES['file']['name']);
            $character = ".";
            $imageFormat = strstr($filename, $character);   

            $newFilename = $_POST['appName'].$imageFormat;
            $targetPath = $newUploadDir . $newFilename;

            //check file extension
            $allowed = array('png');
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowed)) {
                http_response_code(400);
                echo "File Extension .png required";
            } else {
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    echo "File uploaded successfully.";
                } else {
                    http_response_code(500);
                    echo "Failed to upload file.";
                }
            }

        } else {
            http_response_code(400);
            echo "No file uploaded.";
        }
    }

?>