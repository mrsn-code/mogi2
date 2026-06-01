<header class="header">
    <div class="header__logo">
        <a href="{{ route('admin.attendance.list') }}"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
    </div>
    <nav class="header__nav">
        <ul>
            <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
            <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
            <li><a href="{{ route('attendance.request.index') }}">申請一覧</a></li>
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button class="header__logout">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
</header>