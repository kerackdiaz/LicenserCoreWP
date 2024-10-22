document.addEventListener('DOMContentLoaded', function() {
    var activeTab = localStorage.getItem('activeTab');
    if (activeTab) {
        openTab(null, activeTab);
    } else {
        openTab(null, 'welcome'); 
    }
});

function openTab(evt, tabName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("nav-tab");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" nav-tab-active", "");
    }
    document.getElementById(tabName).style.display = "block";
    if (evt) {
        evt.currentTarget.className += " nav-tab-active";
    } else {
        document.querySelector('.nav-tab[href="#' + tabName + '"]').className += " nav-tab-active";
    }
    localStorage.setItem('activeTab', tabName);
}