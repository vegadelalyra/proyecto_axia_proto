import React, { useState } from 'react';

const ExpandableList = ({ data, searchTerm }) => {
  const [expandedTitles, setExpandedTitles] = useState({});
  const [selectedSubtitle, setSelectedSubtitle] = useState(null);
  const [allExpanded, setAllExpanded] = useState(false);

  const toggleTitle = title => {
    setExpandedTitles(prev => ({
      ...prev,
      [title]: !prev[title],
    }));
  };

  const toggleAll = () => {
    const nextExpandState = !allExpanded;
    setAllExpanded(nextExpandState);
    const newExpandedStates = {};
    data.forEach(item => {
      newExpandedStates[item.title] = nextExpandState;
    });
    setExpandedTitles(newExpandedStates);
  };

  const filterData = data => {
    if (!searchTerm) return data;

    return data
      .map(item => {
        const filteredSubtitles = item.subtitles.filter(subtitle =>
          subtitle.toLowerCase().includes(searchTerm.toLowerCase())
        );
        if (filteredSubtitles.length > 0) {
          return { ...item, subtitles: filteredSubtitles };
        }
        return null;
      })
      .filter(item => item !== null);
  };

  const filteredData = filterData(data);

  return (
    <div className='expandable-list'>
      <img
        src='/src/assets/icons/expand.svg'
        alt='Toggle List'
        className='global-expand-icon'
        onClick={toggleAll}
      />
      <div className='expandable-list'>
        {filteredData.map(item => (
          <div key={item.title} className='title-container'>
            <div className='title' onClick={() => toggleTitle(item.title)}>
              {item.title}
            </div>
            {expandedTitles[item.title] && (
              <div className='subtitles'>
                {item.subtitles.map((subtitle, index) => (
                  <div
                    key={index}
                    className={`subtitle ${
                      selectedSubtitle === subtitle ? 'selected' : ''
                    }`}
                    onClick={() => setSelectedSubtitle(subtitle)}>
                    {subtitle}
                  </div>
                ))}
              </div>
            )}
          </div>
        ))}
      </div>
    </div>
  );
};

export default ExpandableList;
