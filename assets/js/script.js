// script.js - interactions simples (dropdown)
document.addEventListener('click', function(e){
  // open/close dropdown lists
  if(e.target.matches('.dropdown-btn') || e.target.closest('.dropdown-btn')){
    const btn = e.target.closest('.dropdown-btn');
    const list = btn.nextElementSibling;
    if(list) list.style.display = (list.style.display === 'block') ? 'none' : 'block';
  } else {
    // close all if clicked outside
    document.querySelectorAll('.dropdown-list').forEach(l => l.style.display = 'none');
  }
});
