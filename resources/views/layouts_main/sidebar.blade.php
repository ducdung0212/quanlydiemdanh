<div class="section-menu-left">
    <div class="box-logo">
    <!-- Completely new logo with different ID -->
    <a href="{{ route('dashboard') }}" id="stu-logo-main" style="display: block !important;">
        <img src="{{ asset('images/logo/STU_logo.webp') }}" alt="STU" 
             id="main-logo-img"
             style="display: block !important; visibility: visible !important; opacity: 1 !important; height: 150px !important; width: auto !important; max-width: 300px !important; object-fit: contain !important;">
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
                <li class="menu-item has-children">
                    <a href="javascript:void(0);" class="menu-item-button">
                        <div class="icon">
                            <img src="{{ asset('images/icon/student.svg') }}" alt="Student" class="svg-icon">
                        </div>
                        <div class="text">Student</div>
                    </a>
                    <ul class="sub-menu">
                        <li class="sub-menu-item">
                            <a href="{{ route('student') }}" class="">
                                <div class="text">Add Student</div>
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a href="{{-- {{ route('brands') }} --}}" class="">
                                <div class="text">Brands</div>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Teacher (đã đổi từ Category) -->
                <li class="menu-item has-children">
                    <a href="javascript:void(0);" class="menu-item-button">
                        <div class="icon">
                            <img src="{{ asset('images/icon/teacher.svg') }}" alt="Teacher" class="svg-icon">
                        </div>
                        <div class="text">Teacher</div>
                    </a>
                    <ul class="sub-menu">
                        <li class="sub-menu-item">
                            <a href="{{ route('teacher') }}" class="">
                                <div class="text">Add Teacher</div>
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a href="{{ route('phancong') }}" class="">
                                <div class="text">Phân Công </div>
                            </a>
                        </li>
                         <li class="sub-menu-item">
                            <a href="{{-- {{ route('diemdanh') }} --}}" class="">
                                <div class="text">Điểm Danh </div>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Order -->
                <li class="menu-item has-children">
                    <a href="javascript:void(0);" class="menu-item-button">
                        <div class="icon"><i class="icon-file-plus"></i></div>
                        <div class="text">Order</div>
                    </a>
                    <ul class="sub-menu">
                        <li class="sub-menu-item">
                            <a href="{{-- {{ route('orders') }} --}}" class="">
                                <div class="text">Orders</div>
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a href="{{-- {{ route('order.tracking') }} --}}" class="">
                                <div class="text">Order tracking</div>
                            </a>
                        </li>
                    </ul>
                </li>
                
                <!-- Các menu item khác -->
                <li class="menu-item">
                    <a href="{{-- {{ route('slider') }} --}}" class="">
                        <div class="icon"><i class="icon-image"></i></div>
                        <div class="text">Slider</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="">
                        <div class="icon"><i class="icon-grid"></i></div>
                        <div class="text">Coupons</div>
                    </a>
                </li>

                <!-- Settings -->
                <li class="menu-item">
                    <a href="{{-- {{ route('settings') }} --}}" class="">
                        <div class="icon"><i class="icon-settings"></i></div>
                        <div class="text">Settings</div>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>