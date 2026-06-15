import { initializeApp } from 'firebase/app';
import { getMessaging } from 'firebase/messaging';

const firebaseConfig = {
    apiKey: "AIzaSyCQh0hM9glpqQcYMxmygzpz6bv_D0ZBLbg",
    authDomain: "admision-unap.firebaseapp.com",
    projectId: "admision-unap",
    storageBucket: "admision-unap.firebasestorage.app",
    messagingSenderId: "75683577446",
    appId: "1:75683577446:web:9bc3ccc3f50740e300f7a7"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export { messaging };
