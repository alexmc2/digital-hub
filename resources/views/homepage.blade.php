<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

    <h1>Hello, this is a blade template</h1>
    <a href="/about">Go to the about page!</a>

    <h1 style="font-size:4em">My name is {{ $name }}</h1>

    <p style="font-size:8em">The year is {{ date('Y')}} </p>
    
</body>
</html>
