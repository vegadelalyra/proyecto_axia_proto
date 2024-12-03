import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';

// Expandable/shrinkable list of titles and subtitles
const ExpandableList = ({ data, searchTerm }) => {
  const navigate = useNavigate();

  const handleSubtitleClick = subtitle => {
    console.log(subtitle);
    setSelectedSubtitle(subtitle.name);
    navigate(subtitle.path);
  };

  // *panel memoization: for better ux, last expanded titles are stored
  // PANEL MEMOIZATION*: loads the last state of panel saved on browser
  const [expandedTitles, setExpandedTitles] = useState(() => {
    const storedPanel = localStorage.getItem('axiaPanel');
    return storedPanel ? JSON.parse(storedPanel) : { firstTime: true };
  });
  const [selectedSubtitle, setSelectedSubtitle] = useState(() => {
    const storedSection = localStorage.getItem('axiaSection');
    return storedSection ? JSON.parse(storedSection) : {};
  });
  const [allExpanded, setAllExpanded] = useState(false);

  // PANEL MEMOIZATION*: saves last state of panel expanded titles
  useEffect(() => {
    localStorage.setItem('axiaPanel', JSON.stringify(expandedTitles));
  }, [expandedTitles]);

  // PANEL MEMOIZATION*: saves last state of panel selected subtitle
  useEffect(() => {
    localStorage.setItem('axiaSection', JSON.stringify(selectedSubtitle));
  }, [selectedSubtitle]);

  // click titles to toggle subtitles visibility
  const toggleTitle = title => {
    setExpandedTitles(prev => ({
      ...prev,
      [title]: !prev[title],
    }));
  };

  // click button to toggle all subtitles visibility
  const toggleAll = () => {
    const nextExpandState = !allExpanded;
    setAllExpanded(nextExpandState);
    const newExpandedStates = {};
    Object.values(data).forEach(item => {
      newExpandedStates[item.title] = nextExpandState;
    });
    setExpandedTitles(newExpandedStates);
  };

  // input for searching a specific subtitle/section of the gmao app
  const filterData = data => {
    if (!searchTerm) return Object.values(data); // Ensure we return an array when no search term

    return Object.values(data)
      .map(item => {
        const filteredSubtitles = item.subtitles.filter(subtitle =>
          subtitle.name.toLowerCase().includes(searchTerm.toLowerCase())
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
        className={`global-expand-icon ${
          expandedTitles['firstTime'] ? 'animated' : ''
        }`}
        onClick={toggleAll}
      />
      <div className='expandable-list'>
        {filteredData.map(item => (
          <div key={item.title} className='title-container'>
            <div className='title' onClick={() => toggleTitle(item.title)}>
              <h5>{item.title}</h5>
            </div>
            {expandedTitles[item.title] && (
              <div className='subtitles'>
                {item.subtitles.map((subtitle, index) => (
                  <div
                    key={index}
                    className={`subtitle ${
                      selectedSubtitle === subtitle.name ? 'selected' : ''
                    }`}
                    onClick={() => handleSubtitleClick(subtitle)}>
                    {subtitle.name}
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
