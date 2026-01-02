document.addEventListener("DOMContentLoaded", function () {
    const regBtn = document.getElementById("regular");
    const homeBtn = document.getElementById("home");
    const emerBtn = document.getElementById("emergency");

    const regForm = document.getElementById("reg");
    const homeForm = document.getElementById("homeService");
    const emerForm = document.getElementById("emerg");

    function hideAll() {
        regForm.style.display = "none";
        homeForm.style.display = "none";
        emerForm.style.display = "none";
    }

    hideAll();

    regBtn.addEventListener("click", function () {
        hideAll();
        regForm.style.display = "block";
    });

    homeBtn.addEventListener("click", function () {
        hideAll();
        homeForm.style.display = "block";
    });

    emerBtn.addEventListener("click", function () {
        hideAll();
        emerForm.style.display = "block";
    });
});
