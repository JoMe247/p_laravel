const optionMenu = document.querySelector(".select-menu"),
  selectBtn = optionMenu.querySelector(".select-btn"),
  options = optionMenu.querySelectorAll(".option"),
  sBtn_text = optionMenu.querySelector(".sBtn-text");

  
// selectBtn.addEventListener("click", () =>
  
// );

function toggleClass(){
  optionMenu.classList.toggle("active");
  document.getElementById("table-drop").style.display = "";
}



options.forEach((option) => {
  option.addEventListener("click", () => {

    let selectedOption = option.innerHTML;
  
    if(selectedOption.indexOf('Close') >= 0){
      sBtn_text.innerHTML = "Quick Action";

      document.getElementById("allAction-btn").setAttribute("status", "pending");
      document.getElementById("allAction-btn").removeAttribute("onclick");

      
    }else{
      sBtn_text.innerHTML = selectedOption;

      document.getElementById("allAction-btn").removeAttribute("status");
      document.getElementById("allAction-btn").setAttribute("onclick", "sendTableData()");

      
    }

    document.getElementById("table-drop").style.display = "none";
    optionMenu.classList.remove("active");  
  });
});


function checkAll(ele) {
  var checkboxes = document.querySelectorAll('#clients-list tr:not([style*="display:none"]):not([style*="display: none"]) input');
  if (ele.checked) {
      for (var i = 0; i < checkboxes.length; i++) {
          if (checkboxes[i].type == 'checkbox') {
              checkboxes[i].checked = true;
              document.getElementById("table-menu").style.zIndex = "6";

              document.getElementsByClassName("select-menu")[0].removeAttribute("status");
              document.getElementById("action-drop-button").addEventListener("click",  toggleClass);
              document.getElementById("selectAll-chk").style.accentColor = "#a43737";
              // document.getElementById("allAction-btn").setAttribute("onclick", "sendTableData()");
                 
          }
      }
  } else {
      for (var i = 0; i < checkboxes.length; i++) {
          // console.log(i)
          if (checkboxes[i].type == 'checkbox') {
              checkboxes[i].checked = false;
              // document.getElementById("table-menu").style.display = "none";
              document.getElementsByClassName("select-menu")[0].setAttribute("status", "pending-drop");
              document.getElementsByClassName("select-menu")[0].classList.remove("active");
              document.getElementById("action-drop-button").removeEventListener("click",  toggleClass);
              sBtn_text.innerText = "Select your option";
              document.getElementById("allAction-btn").setAttribute("status", "pending");
              document.getElementById("allAction-btn").removeAttribute("onclick");
              document.getElementById("table-menu").style.zIndex = "";
          }
      }
  }
}

function checkboxActive(){

  let totalChecks = document.querySelectorAll('#customers-table input[name="customer_select"]:checked').length;
  let totalInputsChecks = document.querySelectorAll('#customers-table input[name="customer_select"]').length;

  if (totalChecks >= 1){
    document.getElementsByClassName("select-menu")[0].removeAttribute("status");
    document.getElementById("action-drop-button").addEventListener("click",  toggleClass);
    document.getElementById("table-menu").style.zIndex = "6";
  }else{
    document.getElementById("allAction-btn").setAttribute("status", "pending");
    document.getElementById("allAction-btn").removeAttribute("onclick");
    document.getElementsByClassName("select-menu")[0].setAttribute("status", "pending-drop");
    document.getElementsByClassName("select-menu")[0].classList.remove("active");
    document.getElementById("action-drop-button").removeEventListener("click",  toggleClass);
    sBtn_text.innerText = "Select your option";
    document.getElementById("selectAll-chk").style.accentColor = "#a43737";
    document.getElementById("table-menu").style.zIndex = "";
  }

  if(totalInputsChecks == totalChecks){
    document.getElementById("selectAll-chk").style.accentColor = "#a43737";
  }else{
    document.getElementById("selectAll-chk").style.accentColor = "#898989";
  }
}