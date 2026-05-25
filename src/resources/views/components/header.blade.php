<header class="header">
    <div class="header__logo">
        <a href="/"><img src="{{ asset('img/logo.png') }}" alt="ロゴ"></a>
    </div>
    @if( !in_array(Route::currentRouteName(), ['register', 'login', 'verification.notice']) )
    <form class="header_search" action="/" method="get">
        @csrf
        <input id="inputElement" class="header_search--input" type="text" name="search" placeholder="なにをお探しですか？">
        <button id="buttonElement" class="header_search--button">
            <img src="{{ asset('img/search_icon.jpeg') }}" alt="検索アイコン" style="height:100%;">
        </button>
    </form>
    <nav class="header__nav">
        <ul>
            <li><a href="">勤怠</a></li>
            <li><a href="">勤怠一覧</a></li>
            <li><a href="">申請</a></li>
            <li>
                <form action="/logout" method="post">
                    @csrf
                    <button class="header__logout">ログアウト</button>
                </form>
            </li>
        </ul>
    </nav>
    @endif
</header>