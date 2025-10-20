<div class="section-menu-left">
    <div class="box-logo">
    <!-- Completely new logo with different ID -->
    <a href="{{ route('dashboard') }}" id="stu-logo-main" style="display: block !important;">
        <img src="{{ asset('images/logo/STU_logo.webp') }}" alt="STU" 
             id="main-logo-img"
             style="display: block !important; visibility: visible !important; opacity: 1 !important; height: 100px !important; width: auto !important; max-width: 330px !important; object-fit: contain !important;">
    </a>
   
    <!-- Keep old logo hidden -->
    <a href="{{ route('dashboard') }}" id="site-logo-inner" style="display: none !important;">
        <img id="logo_header" alt="" src="{{ asset('images/logo/STU_logo.webp') }}"
             data-light="{{ asset('images/logo/STU_logo.webp') }}" 
             data-dark="{{ asset('images/logo/STU_logo.webp') }}">        
    </a>
    
    <div class="button-show-hide">
        <i class="icon-menu-left"></i>
    </div>
</div>
    <div class="center">
        <div class="center-item">
            <div class="center-heading">Main Home</div>
            <ul class="menu-list">
                <li class="menu-item">
                    <a href="{{ route('dashboard') }}" class="">
                        <div class="icon"><i class="icon-grid"></i></div>
                        <div class="text">Dashboard</div>
                    </a>
                </li>
            </ul>
        </div>
        <div class="center-item">
            <ul class="menu-list">
                <!-- User -->
                <li class="menu-item">
                    <a href="{{ route('user') }}" class="">
                        <div class="icon"><i class="icon-user"></i></div>
                        <div class="text">User</div>
                    </a>
                </li>
                
                <!-- Student -->
                <li class="menu-item">
                    <a href="{{ route('student') }}" class="menu-item-button">
                        <div class="icon">
                            <img src="{{ asset('images/icon/student.svg') }}" alt="Sinh viên" class="svg-icon">
                        </div>
                        <div class="text">Sinh viên</div>
                    </a>
    
                </li>
                
                <!-- Lecturer -->
                <li class="menu-item ">
                    <a href="{{ route('lecturer') }}" class="menu-item-button">
                        <div class="icon">
                            <img src="{{ asset('images/icon/lecturer.svg') }}" alt="Giảng viên" class="svg-icon">
                        </div>
                        <div class="text">Giảng viên</div>
                    </a>
                </li>
                <!-- Exam_schedule -->
                 <li class="menu-item ">
                    <a href="{{ route('exam-schedules') }}" class="menu-item-button">
                        <div class="icon">
                            <img src="{{ asset('images/icon/exam_schedule.svg') }}" alt="Lịch thi" class="svg-icon">
                        </div>
                        <div class="text">Lịch thi</div>
                    </a>
                </li>
                </li>
                <!-- Settings -->
                <li class="menu-item">
                    <a href="{{-- {{ route('settings') }} --}}" class="">
                        <div class="icon"><i class="icon-settings"></i></div>
                        <div class="text">Cài đặt</div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>