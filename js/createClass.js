document.getElementById("createClassForm").addEventListener("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    try {
        const response = await fetch("/php/createClass.php", {
            method: "POST",
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        //Спроба розібрати JSON, коректно обробляти відповіді, що не є JSON
        let result;
        try {
            result = await response.json();
        } catch (error) {
            throw new Error("Invalid JSON response");
        }

        if (result.status === "success") {
            document.getElementById("successModalBody").innerText = result.message;
            new bootstrap.Modal(document.getElementById("successModal")).show();
            this.reset();
        } else {
            document.getElementById("errorModalBody").innerText = result.message || "An unknown error occurred.";
            new bootstrap.Modal(document.getElementById("errorModal")).show();
        }
    } catch (error) {
        console.error("Error:", error.message);
        document.getElementById("errorModalBody").innerText = error.message || "An error occurred while creating the class.";
        new bootstrap.Modal(document.getElementById("errorModal")).show();
    }
});
