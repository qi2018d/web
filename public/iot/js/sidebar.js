
if(window.location.pathname === '/'){
    $("#home-menu").addClass("active");
}
else if(window.location.pathname === '/map'){
    $("#map-menu").addClass("active");
}
else if(window.location.pathname === '/user'){
    $("#user-menu").addClass("active");
}
else if(window.location.pathname === '/sensor'){
    $("#sensor-menu").addClass("active");
}
else if(window.location.pathname === '/signout'){
    $("#signout-menu").addClass("active");
}
else if(window.location.pathname === '/signin'){
    $("#signin-menu").addClass("active");
}
else if(window.location.pathname === '/developers'){
    $("#developer-menu").addClass("active");
}