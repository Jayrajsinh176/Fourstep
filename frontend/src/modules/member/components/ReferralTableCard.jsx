export default function ReferralTableCard({
  title,
  columns,
  rows,
  isLoading,
  error,
  emptyMessage,
}) {
  return (
    <>
      <div className="text-center mt-6">
        <h1 className="text-3xl font-bold text-[#B0422E]">{title}</h1>
      </div>

      <div className="p-6">
        <div className="bg-white rounded-2xl shadow-md p-8">
          {isLoading && <p className="text-center text-gray-500 mb-4">Loading...</p>}
          {error && <p className="text-center text-red-500 mb-4">{error}</p>}

          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="bg-[#B0422E] text-white font-semibold">
                  {columns.map((column, index) => {
                    const isFirst = index === 0;
                    const isLast = index === columns.length - 1;

                    return (
                      <th
                        key={column.key}
                        className={`py-4 px-8 text-left ${isFirst ? "rounded-l-xl" : ""} ${isLast ? "rounded-r-xl" : ""}`}
                      >
                        {column.header}
                      </th>
                    );
                  })}
                </tr>
              </thead>

              <tbody>
                {!isLoading && rows.length === 0 && (
                  <tr className="border-b border-gray-300 font-medium">
                    <td className="py-6 px-8 text-center" colSpan={columns.length}>
                      {emptyMessage}
                    </td>
                  </tr>
                )}

                {rows.map((row, rowIndex) => (
                  <tr
                    key={row._row_key || row.id || rowIndex}
                    className="border-b border-gray-300 font-medium"
                  >
                    {columns.map((column) => (
                      <td key={column.key} className="py-6 px-8">
                        {column.render(row, rowIndex)}
                      </td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </>
  );
}
