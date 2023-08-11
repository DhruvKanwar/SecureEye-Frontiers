<nav id="sidebar">
    <div class="profile-info">
        <figure class="user-cover-image"></figure>
        <div class="user-info">
            <img src="assets/img/90x90.jpg" alt="avatar">
            <h6 class=""> {{ Auth::user()->name }}</h6>
            <p class="">Web Developer</p>
        </div>
    </div>

    <div class="shadow-bottom"></div>
    <ul class="list-unstyled menu-categories" id="accordionExample">
        <li class="menu active">
            <a href="#dashboard" data-toggle="collapse" aria-expanded="true" class="dropdown-toggle">
                <div class="">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span>Dashboard</span>
                </div>
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-right">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </div>
            </a>
            <ul class="collapse submenu recent-submenu mini-recent-submenu list-unstyled show" id="dashboard" data-parent="#accordionExample">
                <!-- <li>
                    <a href="{{url('daily_report')}}"> Daily Report </a>
                </li> -->
                <li class="active">
                    <a href="{{url('segments_daily_report')}}"> Segment Daily Report </a>
                </li>
                <li class="active">
                    <a href="{{url('send_regional_email')}}"> Send Email to Clients </a>
                </li>
                <!-- <li class="active">
                    <a href="{{url('import_data')}}">Import Data </a>
                </li> -->
            </ul>
        </li>
    </ul>

</nav>