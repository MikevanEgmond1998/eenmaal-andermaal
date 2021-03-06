require('./fontawesome');
require('./bootstrap');
// require('chart.js');

/*  Navigatie rubrieken */
var categoriesMenuElement = document.querySelector("#category-container-parent");
var categoriesCopyElement = document.querySelector("#category-container-copy");

if (categoriesMenuElement) {
    function hideChildren(parent) {
        var children = parent.parentElement.querySelectorAll(":scope>a,:scope>div");
        for (var i = 0; i < children.length; i++) {
            if (children[i].classList.contains("d-block")) {
                children[i].classList.remove("d-block");
                children[i].classList.add("d-none");
            }
        }
    }

    function toggleSubMenu() {
        var children = this.parentElement.querySelectorAll(":scope>a,:scope>div");
        for (var i = 0; i < children.length; i++) {
            if (children[i].classList.contains("d-block")) {
                children[i].classList.remove("d-block");
                children[i].classList.add("d-none");
            } else {
                children[i].classList.remove("d-none");
                children[i].classList.add("d-block");
            }
        }
    }

    var hoverables = document.querySelectorAll(".clickable-submenu");
    for (var i = 0; i < hoverables.length; i++) {
        hoverables[i].addEventListener('click', toggleSubMenu);
    }

    function closeAllHoverablesChildren() {
        for (var i = 0; i < hoverables.length; i++) {
            hideChildren(hoverables[i]);
        }
    }

    document.addEventListener('click', function (event) {
        var isClickInside = categoriesMenuElement.contains(event.target);

        if (!isClickInside) {
            closeAllHoverablesChildren();
        }
    });

    function debounce(func) {
        var timer;
        return function (event) {
            if (timer) clearTimeout(timer);
            timer = setTimeout(func, 100, event);
        };
    }

    function setNavCopySize() {
        categoriesCopyElement.style.height = categoriesMenuElement.offsetHeight + "px";
    }

    window.addEventListener("resize", debounce(function (e) {
        closeAllHoverablesChildren();
        setNavCopySize();
    }));

    window.addEventListener('load', function () {
        setNavCopySize();
    });
}
/*  Navigatie rubrieken einde */

/* Beoordeling rating selecteren */
var ratingDiv = document.querySelector("div.rating");
if (ratingDiv) {
    var ratingInputs = ratingDiv.querySelectorAll(".rating input");
    for (var i = 0; i < ratingInputs.length; i++) {
        ratingInputs[i].addEventListener("click", function () {
            var ratingLabels = ratingDiv.querySelectorAll(".rating .fa-star");
            for (var x = 0; x < ratingLabels.length; x++) {
                if (ratingLabels[x].querySelector("input").value <= this.value) {
                    ratingLabels[x].classList.remove("far", "fa-star");
                    ratingLabels[x].classList.add("fa", "fa-star");
                } else {
                    ratingLabels[x].classList.remove("fa", "fa-star");
                    ratingLabels[x].classList.add("far", "fa-star");
                }
            }
        });
    }
}
/* Beoordeling rating selecteren einde */

/* Bieden */
var btnBid = document.getElementById("btn-bid");
if (btnBid) {
    function loadBids(response) {
        currentBid.innerText = response.currentBid;
        // textBid.value = response.currentBid + 1;
        lastFiveBidsList.innerHTML = response.lastFiveBidsHTML;
        lastFiveBidsList.scrollTop = 0;
    }

    function timeOutSuccessAlert() {
        clearTimeout(alertTimeout);
        alertTimeout = setTimeout(function () {
            document.getElementById("alert-success").className = "alert alert-success d-none"
        }, 3000);
    }

    var alertTimeout;
    var lastFiveBidsList = document.getElementById("last-five-bids-list");
    var currentBid = document.getElementById("auction-current-bid");
    var textBid = document.getElementById("text-bid");
    var auctionId = document.getElementById("auction-id").value;

    function bidFunction() {
        event.preventDefault();
        var bidAmount = textBid.value;

        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if (JSON.parse(this.responseText).success) {
                    document.getElementById("error").innerHTML = ""
                    document.getElementById("success").innerHTML =
                        JSON.parse(this.responseText).success

                    document.getElementById("alert-success").className = "alert alert-success";
                    document.getElementById("alert-danger").className = "alert alert-danger d-none";

                    loadBids(JSON.parse(this.responseText));
                    timeOutSuccessAlert();
                }

                if (JSON.parse(this.responseText).error) {
                    document.getElementById("success").innerHTML = "";
                    document.getElementById("error").innerHTML =
                        JSON.parse(this.responseText).error

                    document.getElementById("alert-success").className = "alert alert-success d-none";
                    document.getElementById("alert-danger").className = "alert alert-danger";
                }
            }
        };
        xhttp.open("GET", "/bid/" + auctionId + "/" + bidAmount, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send();

    }

    document.getElementById("btn-bid").addEventListener("click", bidFunction);
    textBid.addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
            bidFunction();
        }
    });

    var refreshInterval = window.setInterval(refreshData, 1000);

    function refreshData() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if (JSON.parse(this.responseText).success) {
                    loadBids(JSON.parse(this.responseText));
                }

                if (JSON.parse(this.responseText).error) {
                    clearInterval(refreshInterval);
                    document.getElementById("error").innerHTML =
                        JSON.parse(this.responseText).error
                    document.getElementById("alert-danger").className = "alert alert-danger";
                }
            }
        };
        xhttp.open("GET", "/bid/" + auctionId, true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send();
    }
}
/* Bieden einde */

/* Auction rubrieken selectie */
window.categorySelect = function (level) {
    var field = document.querySelector("select[c-level='" + level + "']");
    var fielsNo = parseInt(field.value);
    var fields = document.querySelectorAll("select[name='category[]']");
    for (var i = 0; i < fields.length; i++) {
        if (parseInt(fields[i].getAttribute("c-level")) > parseInt(level)) {
            fields[i].parentNode.remove();
        }
    }
    if (fielsNo > -1) {
        addCategorySelect(fielsNo);
    }
}

function addCategorySelect(categoryId) {
    var fields = document.querySelectorAll("select[name='category[]']");
    var maxId = 0;
    for (var i = 0; i < fields.length; i++) {
        var curInt = parseInt(fields[i].getAttribute("c-level"));
        if (curInt > maxId) {
            maxId = curInt;
        }
    }

    var categorySelectContainer = document.querySelector("#category-select-container");
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var div = document.createElement('div');
            div.innerHTML = this.responseText;
            div.className = "mb-3 col-md-2";
            categorySelectContainer.appendChild(div);
        }
    };
    xhttp.open("GET", "/veilingmaken/categoryselect/" + categoryId + "/" + (maxId + 1), true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send();
}

/* Auction rubrieken selectie einde */

/* Auction live tijd */
var auctionTimes = document.querySelectorAll(".ea-live-time");
var liveTimesInterval;
if (auctionTimes.length) {
    liveTimesInterval = setInterval(updateTimes, 1000);

    function updateTimes() {
        auctionTimes = document.querySelectorAll(".ea-live-time");
        for (var i = 0; i < auctionTimes.length; i++) {
            var now = new Date().getTime();
            var timeLeft = Date.parse(auctionTimes[i].getAttribute("ea-date")) - now;
            var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            var hours = String(Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
            var minutes = String(Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
            var seconds = String(Math.floor((timeLeft % (1000 * 60)) / 1000)).padStart(2, '0');
            var text = "";
            if(auctionTimes[i].hasAttribute("ea-live-time-big")){
                text = "Sluit over ";
            }
            if (timeLeft < 0) {
                text = "Afgelopen";
            } else {
                if (days > 1) {
                    text += days+" dagen ";
                } else if (days === 1) {
                    text += days+" dag "
                }
                text += hours+":"+minutes;
                if(days < 1){
                    text += ":"+seconds
                }
            }
            auctionTimes[i].innerText = text;
        }
    }
}
/* Auction live tijd einde */

/* Admin sidepanel open/close */
var mySidepanel = document.getElementById("mySidepanel");
if(mySidepanel){
    var openbtn = document.getElementById("openbtn");
    var closebtn = document.getElementById("closebtn");

    document.addEventListener('click', function (event) {
        var isClickInsidePanel = mySidepanel.contains(event.target);
        var isClickInsideOpenBtn = openbtn.contains(event.target);

        if (!isClickInsidePanel && !isClickInsideOpenBtn) {
            mySidepanel.style.left = "-250px";
        }
    });

    openbtn.addEventListener("click", function() {
        /* Set the width of the sidebar to 250px (show it) */

        mySidepanel.style.left = "0";
    });
    closebtn.addEventListener("click", function() {
        /* Set the width of the sidebar to 250px (show it) */

        mySidepanel.style.left = "-250px";
    });
}

/*  Rubrieken admin */
var categoriesAdminElement = document.querySelector("#category-container-parent-admin");
var rubriekenSearchForm = document.getElementById("rubriekenSearchForm");

if (categoriesAdminElement && rubriekenSearchForm) {
    function hideChildren(parent) {
        var children = parent.parentElement.querySelectorAll(":scope>a,:scope>div");
        for (var i = 0; i < children.length; i++) {
            if (children[i].classList.contains("d-block")) {
                children[i].classList.remove("d-block");
                children[i].classList.add("d-none");
            }
        }
    }
    function showChildren(parent) {
        var children = parent.parentElement.querySelectorAll(":scope>a,:scope>div");
        for (var i = 0; i < children.length; i++) {
            if (children[i].classList.contains("d-none")) {
                children[i].classList.remove("d-none");
                children[i].classList.add("d-block");
            }
        }
    }

    function toggleSubMenu() {
        if(this.parentElement.classList.contains("category-0")){
            closeAllHoverablesChildren(this);
        }
        var children = this.parentElement.querySelectorAll(":scope>a,:scope>div");
        for (var i = 0; i < children.length; i++) {
            if (children[i].classList.contains("d-block")) {
                children[i].classList.remove("d-block");
                children[i].classList.add("d-none");
            } else {
                children[i].classList.remove("d-none");
                children[i].classList.add("d-block");
            }
        }
    }

    var hoverables = document.querySelectorAll(".clickable-submenu");
    for (var i = 0; i < hoverables.length; i++) {
        hoverables[i].addEventListener('click', toggleSubMenu);
    }

    function closeAllHoverablesChildren(except) {
        for (var i = 0; i < hoverables.length; i++) {
            if(hoverables[i] != except){
                hideChildren(hoverables[i]);
            }
        }
    }

    rubriekenSearchForm.addEventListener("submit", function(e) {
        e.preventDefault();
        var value = e.target[0].value;
        closeAllHoverablesChildren();
        if(value.length >= 2){
            var rChildren = categoriesAdminElement.querySelectorAll("a,span");
            for(var i = 0; i < rChildren.length; i++){
                var curChild = rChildren[i];
                var curVal = curChild.textContent.toLowerCase();
                if(curVal.indexOf(value.toLowerCase()) !== -1){
                    var foundMax = false;
                    var element = curChild;
                    while(element.parentNode && !foundMax){
                        showChildren(element);
                        element = element.parentNode;
                        if(element == categoriesAdminElement){
                            foundMax = true;
                            break;
                        }
                    }
                }
            }
        }
    });
}


/*  Rubrieken admin einde */