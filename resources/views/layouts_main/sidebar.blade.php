<div class="section-menu-left">
    <div class="box-logo">
        <a href="{{ route('dashboard') }}" id="site-logo-inner">

            <img class="" id="logo_header" alt="" src="{{ asset('images/logo/STU_logo.webp') }}"
                data-light="{{ asset('images/logo/STU_logo.webp') }}" data-dark="{{ asset('images/logo/STU_logo.webp') }}">

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
                <!-- User - Đã sửa thành menu đơn -->
                <li class="menu-item">
                    <a href="{{ route('user') }}" class="">
                        <div class="icon"><i class="icon-user"></i></div>
                        <div class="text">User</div>
                    </a>
                </li>
                
                <!-- Student - Vẫn giữ menu con -->
                <li class="menu-item has-children">
                    <a href="javascript:void(0);" class="menu-item-button">
                          <div class="icon"><i class="icon-user"></i></div>
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
                
                <!-- Category - Vẫn giữ menu con -->
                <li class="menu-item has-children">
                    <a href="javascript:void(0);" class="menu-item-button">
                        <div class="icon"><i class="icon-layers"></i></div>
                        <div class="text">Category</div>
                    </a>
                    <ul class="sub-menu">
                        <li class="sub-menu-item">
                            <a href="{{-- {{ route('category.add') }} --}}" class="">
                                <div class="text">New Category</div>
                            </a>
                        </li>
                        <li class="sub-menu-item">
                            <a href="{{-- {{ route('categories') }} --}}" class="">
                                <div class="text">Categories</div>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Order - Vẫn giữ menu con -->
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
                        <div class="text">Coupns</div>
                    </a>
                </li>

                <!-- Đã xóa menu User trùng lặp ở dưới -->
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