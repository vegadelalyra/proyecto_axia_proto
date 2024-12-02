import React from 'react';

const DynamicTable = ({ data }) => {
  return (
    <section>
      <h2>Dynamic Table</h2>
      <table style={{ width: '100%', borderCollapse: 'collapse' }}>
        <thead>
          <tr>
            {Object.keys(data[0]).map((key, index) => (
              <th
                key={index}
                style={{
                  borderBottom: '2px solid #ccc',
                  textAlign: 'left',
                  padding: '10px',
                }}>
                {key}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {data.map((row, index) => (
            <tr key={index}>
              {Object.values(row).map((value, i) => (
                <td
                  key={i}
                  style={{
                    borderBottom: '1px solid #eee',
                    padding: '10px',
                  }}>
                  {value}
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </section>
  );
};

export default DynamicTable;
