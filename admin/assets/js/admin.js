document.addEventListener('DOMContentLoaded', ()=> {
  // toggle events
  document.querySelectorAll('.toggle-status').forEach(chk => {
    chk.addEventListener('change', function(){
      const tr = this.closest('tr');
      const id = tr.getAttribute('data-id');
      const status = this.checked ? '1' : '0';

      fetch('update_status.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id) + '&status=' + encodeURIComponent(status)
      }).then(r => r.json()).then(json => {
        if (json.status !== 'success') {
          alert('Update failed');
          // revert checkbox
          this.checked = !this.checked;
        }
      }).catch(e => {
        console.error(e);
        this.checked = !this.checked;
      });
    });
  });

  // Add button - simple prompt form (you can replace with modal)
  document.getElementById('btnAdd')?.addEventListener('click', ()=>{
    const name = prompt('Item name:');
    if (!name) return;
    const category = prompt('Category (e.g. Main Courses, Beverages):','Main Courses');
    const price = prompt('Price:','0');
    const desc = prompt('Description:','');
    const form = new URLSearchParams();
    form.append('name', name);
    form.append('category', category);
    form.append('price', price);
    form.append('description', desc);

    fetch('add_menu_item.php', {
      method: 'POST',
      body: form
    }).then(r => r.json()).then(j=>{
      if (j.status === 'success') {
        alert('Item added');
        location.reload();
      } else {
        alert('Add failed: ' + (j.message||''));
      }
    });
  });
});
