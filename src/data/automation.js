export const automationConfig = {
  // Preparado para conectar Formspree, EmailJS o un endpoint externo sin cambiar la UI.
  chatbot: {
    enabled: false,
    provider: "",
    mountId: "iaparatodos-chatbot",
  },
  leadCapture: {
    enabled: false,
    endpoint: "",
    method: "POST",
  },
  analytics: {
    enabled: false,
    provider: "",
    trackingId: "",
  },
};

export const buildWhatsAppUrl = (number, message) => {
  const encodedMessage = encodeURIComponent(message);
  const cleanNumber = number.replace(/[^\dA-Za-z]/g, "");
  return cleanNumber
    ? `https://wa.me/${cleanNumber}?text=${encodedMessage}`
    : `https://wa.me/?text=${encodedMessage}`;
};
