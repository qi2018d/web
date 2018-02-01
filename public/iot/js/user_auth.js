function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}
function isPassword(password){
    var regex = /^[0-9a-zA-Z]{8,}$/;
    return regex.test(password);
}
function isUserName(username){
    var regex = /^[0-9a-zA-Z]{4,}$/;
    return regex.test(username);
}

function isValidSignInFormat(username, password){
    //username can be email address or username itself

    if(!(isEmail(username) || isUserName(username))){
        return {status: false, message: "Invalid user email or user name"}
    }

    if(!isPassword(password)){
        return {status: false, message: "Invalid password format"}
    }

    return {status: true}
}
function isValidSignupFormat(email, name, password, confirm_pw){

    if(!isEmail(email)){
        return {status: false, message: "Invalid email format"}
    }
    if(!isUserName(name)){
        return {status: false, message: "Invalid user name format"}
    }
    if(!isPassword(password)){
        return {status: false, message: "Invalid password format"}
    }
    if(password !== confirm_pw){
        return {status: false, message: "Two passwords are different"}
    }

    return {status:true};

}




$("#signup-form").submit(function(){

    // valid user input format
    var email = $("#inputEmail").val();
    var name = $("#inputName").val();
    var password = $("#inputPassword").val();
    var confirm = $("#inputPasswordConfirm").val();
    var birth = $("#inputBirthdate").val();
    var gender = $("input[name='gender']:checked").val();


    var format_check = isValidSignupFormat(email, name, password, confirm);

    if(format_check.status){

        var data = {
            "email": email,
            "username":name,
            "password" : password,
            "birthdate" : birth,
            "gender" : gender
        };
        $.ajax({
            type: "POST",
            url: "/api/user/signup",
            contentType: "application/json",
            data: JSON.stringify(data), // <-- Put comma here
            success: function(result){

                // if sign-in success
                if(result.status === true){
                    alert('success');
                    $.cookie("ver_email", email);
                    window.location = '/signup/validation';
                }
            }
        });
    }
    else{
        alert(format_check.message);
    }
    return false;
});



