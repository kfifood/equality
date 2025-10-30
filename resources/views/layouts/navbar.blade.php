<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <button class="btn btn-sm btn-outline-primary" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="d-flex align-items-center ms-auto">
            <span class="me-3 d-none d-md-block">Halo, <strong>{{ Auth::user()->full_name }}</strong></span>
            <span class="me-3 d-block d-md-none"><strong>{{ Auth::user()->full_name }}</strong></span>
            
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="userDropdown" 
                        data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
@push('styles')
<style>
    .navbar {
        background-color: white;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 1rem 1.5rem;
    }
    
    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-radius: 10px;
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        border-radius: 5px;
        margin: 0.25rem;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush