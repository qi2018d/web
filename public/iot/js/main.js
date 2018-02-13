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

function isValidEmailFormat(email){
    if(!isEmail(email)){
        return {status: false, message: "Invalid email format"}
    }
    return {status: true};
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

function isValidChangepwFormat(current_pw, new_pw, confirm)
{
    if(new_pw !== confirm)
        return {status: false, message: "Confirmation doesn't match with new password"};

    if(!isPassword(new_pw))
        return {status: false, message: "Invalid password format"};

    return {status: true};
}

function isValidForgotpwChangeFormat(new_pw, confirm)
{
    if(new_pw !== confirm)
        return {status: false, message: "Confirmation doesn't match with new password"};

    if(!isPassword(new_pw))
        return {status: false, message: "Invalid password format"};

    return {status: true};
}