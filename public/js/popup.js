 window.addEventListener("load", function() {
    
    setTimeout(function() {
      let popup = document.getElementById("myPopup");

      
      popup.classList.remove("hidden");
      setTimeout(() => popup.classList.add("opacity-100"), 50);

      
      setTimeout(function() {
        
        popup.classList.remove("opacity-50");
        popup.classList.add("opacity-0");

        
        setTimeout(() => {
          popup.classList.add("hidden");
        }, 500);
      }, 5000); 
    }); 
  });