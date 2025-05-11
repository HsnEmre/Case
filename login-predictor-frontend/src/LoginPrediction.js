import React from 'react';

const LoginPredictions = ({ data }) => { //app.s dosyasındaki verileri al
  if (!data || data.length === 0) {
    return <div>Veriler yükleniyor veya hatalı veri</div>;
    //veri yoksa veya hala gelmediyse bu yazı çıkacak aşağısı zaten html css
  }

  return (
    //verileri al ekrana bas return et fonksiyonun geri dönüşü
    <div>
      <table border="1" style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            <th>User ID</th>
            <th>Ad</th>
            <th>Son Giriş Zamanı</th>
            <th>Tahmin (Ortalama Aralık)</th>
            <th>En Yaygın Saat Tahmini</th>
          </tr>
        </thead>
        <tbody>
          {data.map(user => (
            <tr key={user.id}>
              <td>{user.id}</td>
              <td>{user.name}</td>
              <td>{new Date(user.last_login).toLocaleString()}</td>
              <td>{new Date(user.prediction_avg_interval).toLocaleString()}</td>
              <td>{new Date(user.prediction_common_hour).toLocaleString()}</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};

export default LoginPredictions;
//bileşeni farklı yerlerdede kullanmak için 