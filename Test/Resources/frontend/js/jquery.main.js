$(document).ready(function(){

    // var sidebarNavigation =  $($('.sidebar--categories-navigation .sidebar--navigation')[0]);
    // sidebarNavigation.showHide();

    var mainSidebar = $(".sidebar-main");
    mainSidebar.draggable();

    // mainSidebar.draggable({
    //     axis:"x", // miayn x arancqov kareli e texapoxel
    //     axis:"y", // miayn y arancqov kareli e texapoxel
    //     cursor: "move",
    //     cursor: "help",
    //     helper: function(event){ return $("<div>Я элемент помощник.</div>") }, // texapoxelu jamank elementi poxaren haytnvume e text@
    //  });

    // window.StateManager
    //     .removePlugin('.navigation--entry.entry--account.with-slt', 'swDropdownMenu')
    //
    //     .addPlugin('*[data-offcanvas="true"]', 'swOffcanvasMenu', ['m'])
    //
    //     .addPlugin('*[data-subcategory-nav="true"]', 'swSubCategoryNav', ['m'])
    //     .addPlugin('.navigation--entry.entry--account.with-slt', 'swDropdownMenu', [ 'l', 'xl' ])
});
