<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-speedometer"></i>
        <span class="sidebar-brand-text">K-LAB</span>
    </div>
    <ul class="sidebar-nav">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2 nav-icon"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <!-- Master Data Section -->
        <li class="nav-section">
            <span class="nav-section-text">MASTER DATA</span>
        </li>
<li class="nav-item">
    <a href="{{ route('timbangan.index') }}" class="nav-link {{ Request::is('timbangan*') ? 'active' : '' }}">
        <i class="bi bi-speedometer nav-icon"></i>
        <span class="nav-text">Data Timbangan</span>
    </a>
</li>
        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ 
                Request::is('users*') || 
                Request::is('master/users*') ? 'active' : '' }}">
                <i class="bi bi-people nav-icon"></i>
                <span class="nav-text">Manajemen User</span>
            </a>
        </li>
        <li class="nav-item">
    <a href="{{ route('line.index') }}" class="nav-link {{ Request::is('line*') ? 'active' : '' }}">
        <i class="bi bi-diagram-3 nav-icon"></i>
        <span class="nav-text">Master Line</span>
    </a>
</li>

        <!-- Operations Section -->
        <li class="nav-section">
            <span class="nav-section-text">OPERATIONS</span>
        </li>
        <li class="nav-item">
    <a href="{{ route('penggunaan.index') }}" class="nav-link {{ Request::is('penggunaan*') ? 'active' : '' }}">
        <i class="bi bi-arrow-right-circle nav-icon"></i>
        <span class="nav-text">Penggunaan Timbangan</span>
    </a>
</li>
        <li class="nav-item">
    <a href="{{ route('perbaikan.index') }}" class="nav-link {{ Request::is('perbaikan*') ? 'active' : '' }}">
        <i class="bi bi-tools nav-icon"></i>
        <span class="nav-text">Perbaikan Timbangan</span>
    </a>
</li>

        <li class="nav-item">
    <a href="{{ route('riwayat.index') }}" class="nav-link {{ 
        Request::is('monitoring/riwayat*') || 
        Request::is('riwayat*') ? 'active' : '' }}">
        <i class="bi bi-clock-history nav-icon"></i>
        <span class="nav-text">Riwayat Lengkap</span>
    </a>
</li>

        <!-- Reports Section -->
        <li class="nav-section">
            <span class="nav-section-text">LAPORAN</span>
        </li>
        <li class="nav-item">
            <a href="{{ route('laporan.index') }}" class="nav-link {{ 
                Request::is('laporan*') ? 'active' : '' }}">
                <i class="bi bi-graph-up nav-icon"></i>
                <span class="nav-text">Laporan Timbangan</span>
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    display: flex;
    flex-direction: column;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
}

/* Sidebar Brand - FIXED */
.sidebar-brand {
    padding: 1rem 1.5rem;
    font-size: 1.7rem;
    font-weight: 800;
    text-align: center;
    white-space: nowrap;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #fff;
    border-bottom: 1px solid #e9ecef;
    position: sticky;
    top: 0;
    z-index: 1001;
    flex-shrink: 0;
}

.sidebar.mini .sidebar-brand {
    padding: 1rem 0.5rem;
    font-size: 1.2rem;
    justify-content: center;
}

.sidebar-brand i {
    margin-right: 0.5rem;
    transition: margin 0.3s ease;
}

.sidebar.mini .sidebar-brand i {
    margin-right: 0;
}

.sidebar-brand-text {
    transition: opacity 0.3s ease, visibility 0.3s ease;
    opacity: 1;
    visibility: visible;
}

.sidebar.mini .sidebar-brand-text {
    opacity: 0;
    visibility: hidden;
    display: none;
}

/* Sidebar Nav - SCROLLABLE */
.sidebar-nav {
    padding: 0;
    list-style: none;
    margin: 0;
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1;
    max-height: calc(100vh - 80px);
}

/* Custom Scrollbar untuk sidebar */
.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.nav-section {
    padding: 0.5rem 1.5rem;
    margin-top: 1rem;
}

.nav-section-text {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    opacity: 1;
    visibility: visible;
}

.sidebar.mini .nav-section-text {
    opacity: 0;
    visibility: hidden;
    display: none;
}

.nav-item {
    margin: 0.25rem 0;
}

.nav-link {
    color: #4361ee;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    text-decoration: none;
    white-space: nowrap;
    position: relative;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.sidebar.mini .nav-link {
    padding: 0.75rem 1rem;
    justify-content: center;
}

.nav-link:hover {
    color: #fff;
    background-color: rgba(67, 100, 240, 0.7);
}

.nav-link.active {
    color: #fff;
    background-color: rgba(67, 100, 240, 0.7);
    font-weight: 600;
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background-color: #4361ee;
}

.nav-icon {
    margin-right: 0.75rem;
    font-size: 1.25rem;
    min-width: 24px;
    text-align: center;
    transition: margin 0.3s ease;
}

.sidebar.mini .nav-icon {
    margin-right: 0;
}

.nav-text {
    transition: opacity 0.3s ease, visibility 0.3s ease;
    opacity: 1;
    visibility: visible;
}

.sidebar.mini .nav-text {
    opacity: 0;
    visibility: hidden;
    display: none;
}

/* Tooltip untuk mode mini */
.sidebar.mini .nav-link {
    position: relative;
}

.sidebar.mini .nav-link::after {
    content: attr(data-text);
    position: absolute;
    left: 100%;
    top: 50%;
    transform: translateY(-50%);
    background-color: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
    pointer-events: none;
    font-size: 0.875rem;
}

.sidebar.mini .nav-link:hover::after {
    opacity: 1;
    visibility: visible;
    margin-left: 10px;
}
</style>

<script>
// Tambahkan data-text untuk tooltip
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        const navTextElement = link.querySelector('.nav-text');
        if (navTextElement) {
            const text = navTextElement.textContent.trim();
            link.setAttribute('data-text', text);
        }
    });

    console.log('Current path:', window.location.pathname);
    navLinks.forEach(link => {
        if (link.classList.contains('active')) {
            console.log('Active link:', link.href);
        }
    });
});
</script>