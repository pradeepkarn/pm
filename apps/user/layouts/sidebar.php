<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-item">
      <a class="nav-link " href="/<?php echo home; ?>/user">
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->

  
    <!-- Slider components -->

    <li class="nav-item">
      <a class="nav-link <?php echo menu_show("tickets") ? null : "collapsed"; ?>" onclick="setMenu('tickets')" data-bs-target="#components-supports" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i><span>Tickets</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="components-supports" class="nav-content collapse <?php echo menu_show("tickets") ? "show" : null; ?>" data-bs-parent="#sidebar-nav">
        <li>
          <a href="/<?php echo home . route('userSupportList', ['cg' => 'open']); ?>">
            <i class="bi bi-menu-button-wide"></i><span>Running</span>
          </a>
        </li>
        <li>
          <a href="/<?php echo home . route('userSupportList', ['cg' => 'closed']); ?>">
            <i class="bi bi-menu-button-wide"></i><span>Closed</span>
          </a>
        </li>
      </ul>
    </li>
   
    <!-- End Components  -->

  </ul>

</aside>