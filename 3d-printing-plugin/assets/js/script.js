jQuery(document).ready(function ($) {
    var currentStep = 1;
    var processData = {
        'SLS': { A: 10.89, B: 8.44, M: 75, Q: 1.0, materials: ['P12'] },
        'FFF': { A: 10, B: 8, M: 70, Q: 1.0, materials: ['PLA', 'ABS'] },
        'SLM': { A: 0, B: 0, M: 0, Q: 0, materials: ['Edelstahl'] },
        'Poly Jet': { A: 0, B: 0, M: 0, Q: 0, materials: ['Vero-Familie'] },
        'Pulverdüse': { A: 0, B: 0, M: 0, Q: 0, materials: ['Edelstahl', 'Werkzeugstahl'] }
    };
    var selectedProcess = null;
    var userInputs = {};
    var chosenResult = {};

    function showStep(step) {
        $(".step").hide();
        $("#step-" + step).show();
        $(".status-bar .step").removeClass("active");
        $(".status-bar .step:nth-child(" + step + ")").addClass("active");
    }

    function updateMaterialDropdown() {
        var materialSelect = $("#material");
        materialSelect.empty();
        if (selectedProcess) {
            var materials = processData[selectedProcess].materials;
            materials.forEach(function (material) {
                materialSelect.append(new Option(material, material));
            });
        }
    }

    function calculatePrice(process, height, crossSection, volume) {
        const { A, B, M, Q } = processData[process];
        let x;
        if (crossSection < 5625) {
            x = 3.3 * height + 40;
        } else {
            x = height * (3.3 + (1.2325e-4 * crossSection - 0.625)) + 40;
        }
        return (((x / 60) * (A + B) + volume * Q * 1e-6 * M + 109.41) * 1.90625 + 9.23) * 1.19;
    }

    function populateResultsTable() {
        const height = parseFloat(userInputs.height);
        const crossSection = parseFloat(userInputs.crossSection);
        const volume = height * crossSection;
    
        $("#chosen-process").text(userInputs.process);
        $("#summary-height").text(height);
        $("#summary-cross-section").text(crossSection);
        $("#summary-material").text(userInputs.material);
    
        const tbody = $("#price-table tbody");
        tbody.empty();
    
        const prices = Object.keys(processData).map(process => ({
            process,
            price: calculatePrice(process, height, crossSection, volume)
        }));
    
        prices.forEach(({ process, price }) => {
            tbody.append(
                `<tr data-process="${process}" data-price="${price.toFixed(2)}">
                    <td>${process}</td>
                    <td>€${price.toFixed(2)}</td>
                </tr>`
            );
        });
    
        // When a row is clicked
        $("#price-table tbody tr").off("click").on("click", function () {
            $("#price-table tbody tr").removeClass("selected");
            $(this).addClass("selected").css("border", "3px solid blue");

            // Add green checkmark
            $("#price-table tbody tr").find("td").each(function() {
                $(this).find(".checkmark").remove();  // Remove existing checkmarks
            });
            $(this).find("td:first-child").append('<span class="checkmark" style="color: green; font-weight: bold;">&#10004;</span>');

            chosenResult = {
                process: $(this).data("process"),
                price: $(this).data("price")
            };
        });

        // Select the first row and add a green checkmark
        tbody.find("tr").first().addClass("selected").css("border", "3px solid blue");
        tbody.find("tr").first().find("td:first-child").append('<span class="checkmark" style="color: green; font-weight: bold;">&#10004;</span>');
        chosenResult = prices[0];
    }

    $(".next-button").click(function (e) {
        e.preventDefault();
        if (currentStep === 3) {
            userInputs = {
                process: selectedProcess,
                height: $("#height").val(),
                crossSection: $("#cross-section").val(),
                material: $("#material").val()
            };
            populateResultsTable();
        } else if (currentStep === 4) {
            $("#final-height").text(userInputs.height);
            $("#final-cross-section").text(userInputs.crossSection);
            $("#final-material").text(userInputs.material);
            $("#final-process").text(chosenResult.process);
            $("#final-price").text(chosenResult.price);
        } 
        // Collect data for step 5
        if (currentStep === 5) {
            userInputs['first-name'] = $("input[name='first-name']").val();
            userInputs['last-name'] = $("input[name='last-name']").val();
            userInputs['email'] = $("input[name='email']").val();
            userInputs['process'] = selectedProcess;
            userInputs['material'] = $("#material").val();
            userInputs['price'] = chosenResult.price;
            userInputs['height'] = chosenResult.height;
            userInputs['cross-section'] = chosenResult.crossSection;

            var formData = new FormData();
            formData.append('action', 'send_request');
            formData.append('user_inputs', JSON.stringify(userInputs));
            formData.append('file', $("#file-input")[0].files[0]);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        showStep(6);
                    } else {
                        alert('Error in sending the request.');
                    }
                }
            });
        }
        if (currentStep < 6) currentStep++;
        showStep(currentStep);
    });

    $(".back-button").click(function (e) {
        e.preventDefault();
        if (currentStep > 1) currentStep--;
        showStep(currentStep);
    });

    $("input[name='process']").change(function () {
        selectedProcess = $(this).val();
        updateMaterialDropdown();
    });


    $(".choose-file-button").click(function () {
        $("#file-input").click();  
    });


    $("#file-input").change(function () {
        var fileName = $(this).val().split('\\').pop();  
        if (fileName) {
            $("#file-info").show();  
            $("#uploaded-file-name").text(fileName);  
        } else {
            $("#file-info").hide();
        }
    });


    $("#remove-file").click(function () {
        $("#file-input").val('');  
        $("#file-info").hide();  
    });

    showStep(currentStep);
});
