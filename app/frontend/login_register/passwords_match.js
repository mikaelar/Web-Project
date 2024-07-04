document.getElementsByTagName("form")[0].addEventListener('submit', (e) => {
    e.preventDefault();
    const passwordNode = document.getElementById("password");
    const repeatPasswordNode = document.getElementById("confirm_password");
    repeatPasswordNode.style.border = "0px";
    if (passwordNode.value !== repeatPasswordNode.value) {
        repeatPasswordNode.style.border = "1px solid #b3301e"; // make the border red
        alert("Потвърдената парола не съвпада с оригиналната!");
    }
    else {
        e.target.submit(); // send the form, if the passwords match
    }
});