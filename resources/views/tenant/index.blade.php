<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    {{ Auth::guard('tenant')->user()->first_name }}
    wow
    <form action="{{ route('tenant.logout') }}" method="post">
        @csrf
        <button type="submit">ออกจากระบบ</button>
    </form>
</body>
</html>