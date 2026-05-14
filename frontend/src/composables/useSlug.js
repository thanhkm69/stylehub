export function useSlug() {
  const generateSlug = (text) => {
    if (!text) return '';

    return text
      .toString()
      .toLowerCase()
      .normalize('NFD') // Tách dấu ra khỏi ký tự
      .replace(/[\u0300-\u036f]/g, '') // Xóa dấu
      .replace(/[đĐ]/g, 'd') // Thay thế chữ đ
      .replace(/([^0-9a-z-\s])/g, '') // Xóa các ký tự không phải là chữ số, chữ cái, gạch ngang, hoặc khoảng trắng
      .replace(/(\s+)/g, '-') // Thay khoảng trắng bằng gạch ngang
      .replace(/-+/g, '-') // Gom nhiều gạch ngang thành 1
      .replace(/^-+|-+$/g, ''); // Xóa gạch ngang ở đầu và cuối
  };

  return {
    generateSlug,
  };
}
