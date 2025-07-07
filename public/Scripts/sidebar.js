document.addEventListener("DOMContentLoaded", function () {
  lucide.createIcons();

  const sidebar = document.getElementById("sidebar");
  const toggleBtn = document.getElementById("toggleSidebar");
  const iconWrapper = document.getElementById("rotateWrapper"); // rotate this
  const icon = iconWrapper.querySelector("i");
  const logoCollapsed = document.getElementById("logoCollapsed");
  const logoExpanded = document.getElementById("logoExpanded");
  const sidebarLabels = document.querySelectorAll(".sidebar-label");

  let expanded = false;

  toggleBtn.addEventListener("click", () => {
    expanded = !expanded;

    // Sidebar width
    sidebar.classList.toggle("w-[8%]", !expanded);
    sidebar.classList.toggle("w-[20%]", expanded);

    // Logo transitions
    logoCollapsed.classList.toggle("opacity-0", expanded);
    logoCollapsed.classList.toggle("opacity-100", !expanded);
    logoExpanded.classList.toggle("opacity-100", expanded);
    logoExpanded.classList.toggle("opacity-0", !expanded);

    // Label show/hide
    sidebarLabels.forEach(label => {
      label.classList.toggle("hidden", !expanded);
      label.classList.toggle("inline", expanded);
    });

    // Rotate button wrapper
    iconWrapper.classList.toggle("rotate-180", expanded);

    // Change icon direction (chevrons-left/right)
    icon.setAttribute("data-lucide", expanded ? "chevrons-left" : "chevrons-left");
    lucide.createIcons(); // re-render icon only
  });
});