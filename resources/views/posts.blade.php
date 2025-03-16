<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>Hello! Is it blade</div>
    @foreach($posts as $post)
        <div>{{$post->title}}</div>
        <a href="{{route('main.index1')}}">Main</a>
    @endforeach
</body>
</html>