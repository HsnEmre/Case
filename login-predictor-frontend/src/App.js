import React, { useEffect, useState } from 'react';
import axios from 'axios';
import LoginPredictions from './LoginPrediction'; // LoginPredictions bileşenini dahil ediyoruz

const App = () => {
  const [userData, setUserData] = useState([]);

  // API'den veri çekme
  useEffect(() => {
    axios.get('http://localhost:8000/predictions.php') // PHP API URL'niz
      .then(response => {
        setUserData(response.data);  // API'den gelen veriyi state'e ekle
      })
      .catch(error => {
        console.error('Veri alırken hata oluştu:', error);
      });
  }, []);

  return (
    <div>
      <h1>Kullanıcı Giriş Verileri ve Tahminler</h1>

      {/* LoginPredictions bileşenine userData'yı props olarak gönderiyoruz */}
      <LoginPredictions data={userData} />
    </div>
  );
};

export default App;
