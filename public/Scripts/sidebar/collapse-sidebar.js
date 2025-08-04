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

    console.log("existing!" , icon)

  toggleBtn.addEventListener("click", () => {
    expanded = !expanded;

    // Sidebar width
    sidebar.classList.toggle("w-[100px]", !expanded);
    sidebar.classList.toggle("w-[300px]", expanded);

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
    icon.setAttribute(expanded ? "chevrons-left" : "chevrons-right");
    lucide.createIcons(); // re-render icon only
  });
});