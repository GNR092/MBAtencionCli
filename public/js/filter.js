function myFunction() {
    let input = document.getElementById("myInput");
    let filter = input.value.toUpperCase();
    let ul = document.getElementById("myUL");
    let li = ul.getElementsByTagName("li");

    
    if (input.value.trim() === "") {
        ul.classList.add("hidden");
        return;
    } else {
        ul.classList.remove("hidden");
    }

    
    for (let i = 0; i < li.length; i++) {
        li[i].style.display = "";
    }

    
    if (filter.length > 0) {
        for (let i = 0; i < li.length; i++) {
            let a = li[i].getElementsByTagName("a")[0];
            let txtValue = a.textContent || a.innerText;
            li[i].style.display = txtValue.toUpperCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
}

function selectUser(id, name) {
    document.getElementById("myInput").value = name;  
    document.getElementById("selectedUserId").value = id; 
    document.getElementById("myUL").classList.add("hidden"); 
}