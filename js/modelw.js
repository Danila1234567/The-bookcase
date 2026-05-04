function openEditModal(id, text) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_text').value = text;
    document.getElementById('editModal').style.display = 'block';
}