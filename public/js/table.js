document.getElementById("tableSearch").addEventListener('input', function (evt) {
    buscarUsuario();
});

function buscarUsuario(){

    jQuery.expr[':'].contains = function(a, i, m) {
        return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
    };

    var tag = document.getElementById("tableSearch").value;
    
    if(tag.length > 3){

        $('#clients-list tr:not(:contains('+tag+'))').not("#table-header").css( "display", "none" );       
        $('#clients-list tr:contains('+tag+')').not("#table-header").css( "display", "" );
           
    }else if(tag.length == 0){

        $('#clients-list tr').not("#table-header").css( "display", "" );
        
    }
    
    
}


//Función para Crear Inputs 

function arrayToInputs(inputArray) {
    // Create a container div
    const container = document.createElement('div');
    container.id = 'customer-inputs';

    // Iterate over the inputArray
    inputArray.forEach((item, index) => {
        // Split each item by commas
        const values = item.split(',');

        // Create a row div for each array element
        const rowDiv = document.createElement('div');
        rowDiv.classList.add('customer-row');

        // Create an input element for each value
        values.forEach((value, i) => {
            const input = document.createElement('input');

            let dataName = value.split(':')[0];
            let dataValue = value.split(':')[1];

            // Create a row div for each array element
            const icon = document.createElement('p');
            icon.classList.add('input-name-upper');
            icon.innerText = dataName;

            // Create a row div for each array element
            const inputContainer = document.createElement('label');
            inputContainer.classList.add('input-container-row');

            //Input Type
            input.setAttribute('data-value', dataName);
            
            input.setAttribute("name", `${dataName}[]`);
            input.value = dataValue.trim();

            // Set a unique identifier for each input, e.g., array[0][comma 1]
            input.setAttribute('data-index', `row[${index}]${dataName}[${index}]`);

            // Append the input to the row div
            inputContainer.appendChild(icon);
            inputContainer.appendChild(input);
            rowDiv.appendChild(inputContainer);
        });

        // Append the row div to the container
        container.appendChild(rowDiv);
    });
  
    // Append the container to the document body or another target element
    document.getElementById("outer-box").appendChild(container);
    document.getElementById("edit-customer").style.display = "flex";

    setTimeout(function(){
        document.getElementById("outer-box").style.transform = "translateY(0%)";
    },100);
    
  }



/* Function to get Values as array */

// Getting All Values from the Selected items on the table //
let nameArr;
let finalArray;

function sendTableData(){

    let nameArr = [];

    let selectedRows = document.querySelectorAll('#clients-list tr:not([style*="display:none"]):not([style*="display: none"]) .table-name').length;
    let rowsCounter = 0;
    let inputChecked;
    let actionSelected = document.querySelector("#action-drop-button > span > span").innerText;

    console.log("--------------- " + actionSelected + " ---------------");

    for(rowsCounter; rowsCounter <= selectedRows-1; rowsCounter++){
        
        inputChecked = document.getElementsByName("customer_select")[rowsCounter].checked;

        if(inputChecked){

            // console.log("Row Number " + rowsCounter);

            let clientId = document.getElementsByClassName("customer-id")[rowsCounter].innerText;
            let clientName = document.getElementsByClassName("table-name")[rowsCounter].innerText;
            let clientPolicy = document.getElementsByClassName("customer-policy")[rowsCounter].innerText;
            let clientAddress = document.getElementsByClassName("customer-address")[rowsCounter].innerText;
            let clientPhone = document.getElementsByClassName("customer-phone")[rowsCounter].innerText;
            let clientDob = document.getElementsByClassName("customer-dob")[rowsCounter].innerText;

            nameArr.push("id:"+clientId + "," + "name:"+clientName + "," + "policy:"+clientPolicy + "," + "address:"+clientAddress + "," + "phone:"+clientPhone + "," + "dob:"+clientDob);

        }else{
            // console.log("---------------- Not Selected ----------------");
        } 
    }

    finalArray = nameArr;

    var answer = window.confirm(`Do you want to ${actionSelected} to selected customers?`);
    if (answer) {
        //some code
        $(document).ready(function(){
                        
            $.ajax({
                url:'operations/table/multiple.php',
                type:'post',
                data: {finalArray: finalArray}, 
                success: function(result){
                    // $("#search-result-2").html(result);
                    console.log("---------- Result From PHP -----------");
                    console.log(result);
                    // location.reload();
                }
            });
                
        });

        arrayToInputs(finalArray);
    }
    else {
        //some code
    }

   console.log("---------- Result From JS -----------");
   console.log(finalArray);

//    arrayToInputs(finalArray);
}

