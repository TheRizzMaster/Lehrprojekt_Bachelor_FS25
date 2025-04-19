fetch("./data/example.json")
.then((response) => response.json())
.then((data) => {

    console.log(data);

    const result = document.querySelector("#result");

    result.innerHTML = data.theory[0].kapitel;

})
.catch((error) => console.error(error));

fetch("./data/example.json")
.then((response) => response.json())
.then((data) => {

    const result = document.querySelector("#result");

    data.theory.forEach((item) => {
        const p = document.createElement("p");
        p.textContent = item.kapitel;
        result.appendChild(p);
    });

})
.catch((error) => console.error(error));
