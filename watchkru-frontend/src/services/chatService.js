import api from "./api";
import { IS_STATIC } from "./mode";
import { staticServices } from "./staticBackend";

const realChatService = {
  groups() {
    return api.get("/chat/groups").then((r) => r.data.data);
  },
  createGroup(payload) {
    return api.post("/chat/groups", payload).then((r) => r.data);
  },
  messages(groupId, afterId) {
    return api
      .get(`/chat/groups/${groupId}/messages`, { params: afterId ? { after_id: afterId } : {} })
      .then((r) => r.data.data);
  },
  send(groupId, body) {
    return api.post(`/chat/groups/${groupId}/messages`, { body }).then((r) => r.data);
  },
  markRead(groupId) {
    return api.post(`/chat/groups/${groupId}/read`).then((r) => r.data);
  },
  members(groupId) {
    return api.get(`/chat/groups/${groupId}/members`).then((r) => r.data.data);
  },
  requestDeletion(groupId) {
    return api.post(`/chat/groups/${groupId}/deletion-request`).then((r) => r.data);
  },
};

const realNotificationService = {
  list() {
    return api.get("/user/notifications").then((r) => r.data.data);
  },
  markAllRead() {
    return api.post("/user/notifications/read").then((r) => r.data);
  },
};

const realContactService = {
  send(payload) {
    return api.post("/contact", payload).then((r) => r.data);
  },
};

export const chatService = IS_STATIC ? staticServices.chat : realChatService;
export const notificationService = IS_STATIC ? staticServices.notifications : realNotificationService;
export const contactService = IS_STATIC ? staticServices.contact : realContactService;
