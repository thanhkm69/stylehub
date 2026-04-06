import { useToast } from 'vue-toastification'
export const useNotify = () => {
  const toast = useToast()
  return {
    success: (msg) => toast.success(msg),
    error: (msg) => toast.error(msg),
    warn: (msg) => toast.warning(msg),
    info: (msg) => toast.info(msg),
  }
}
