let d = document;
let q = function(q) { return d.querySelector(q)};

let initDump = function(dumpClass) {
  let box = q("." + dumpClass);
  box.querySelectorAll(".row.obj > .key, .row.arr > .key").forEach(function(item) {
    item.addEventListener('click', (e) => {
      e.currentTarget.parentElement.classList.toggle("open");
    });
  });
}