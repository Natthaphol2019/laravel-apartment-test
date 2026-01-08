@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <h1>สวัสดี Admin Dashboard</h1>
    <form action="{{ route('admin.logout') }}" method="post">
        @csrf
        <button type="submit">ออกจากระบบ</button>
    </form>

@endsection