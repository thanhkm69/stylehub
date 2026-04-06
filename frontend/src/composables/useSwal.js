import Swal from 'sweetalert2'
export const swalConfirmDelete = (title, text) =>
  Swal.fire({
    title,
    text,
    icon: 'warning',
    confirmButtonText: 'Xóa',
    confirmButtonColor: '#d33',
    cancelButtonText: 'Hủy',
    showCancelButton: true,
    showCloseButton: true,
  })
