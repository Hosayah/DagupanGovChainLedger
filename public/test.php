
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../frontend/dist/output.css">
    <link rel="stylesheet" href="./dist/admin/dashboard.php">
</head>
<body>
    <!-- upload.html -->
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="document" required />
        <button type="submit">Upload to IPFS via Pinata</button>
    </form>
</body>
</html>